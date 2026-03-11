# rajin-auth

A standalone **OAuth 2.0 / OpenID Connect Identity Provider (IdP)** built with Laravel 12 and Passport 12. Acts as a central authentication server for a suite of applications — each app delegates login to this service instead of managing its own users and passwords.

## Stack

| Concern | Package | Version |
|---|---|---|
| Framework | `laravel/framework` | `^12.0` |
| PHP | — | 8.5 |
| OAuth 2.0 server | `laravel/passport` | `^12.0` |
| Google social login | `laravel/socialite` | `^5.0` |
| Frontend | Tailwind CSS via Vite | — |
| Database | SQLite (dev) / MySQL / PostgreSQL | — |

## What it does

- Email + password registration and login
- Google OAuth login (with automatic account merging)
- Issues **access tokens and refresh tokens** to registered client apps via OAuth 2.0
- Standard **OIDC `/api/userinfo`** endpoint — any client identifies the logged-in user with a Bearer token
- Admin panel to register and manage OAuth client apps

---

## Installation

### Prerequisites

- PHP 8.2+
- Composer 2.4+
- Node.js 18+
- MySQL 8.0+ / PostgreSQL 14+ (SQLite for local dev)

### Step 1 — Clone and install dependencies

```bash
git clone <repo-url> rajin-auth
cd rajin-auth

composer install
npm install
```

> If Composer blocks installation due to a `firebase/php-jwt` security advisory, add the following inside the `"config"` block in `composer.json` and retry:
> ```json
> "config": {
>     "audit": { "block-insecure": false }
> }
> ```

### Step 2 — Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your values:

```ini
APP_NAME="Rajin Auth"
APP_URL=https://auth.yourdomain.com
APP_ENV=production
APP_DEBUG=false

# Database (switch from sqlite for production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rajin_auth
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Google OAuth (from Google Cloud Console)
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-...
GOOGLE_REDIRECT_URI=https://auth.yourdomain.com/auth/google/callback

# Token expiry
PASSPORT_TOKEN_EXPIRE_IN=1440       # 24 hours
PASSPORT_REFRESH_TOKEN_EXPIRE_IN=43200  # 30 days
```

### Step 3 — Database migrations

```bash
php artisan migrate
```

### Step 4 — Passport setup

```bash
php artisan passport:install --uuids
```

This generates `storage/oauth-private.key` and `storage/oauth-public.key` and creates the default Passport clients. **Save the output** — the client secrets shown here are hashed in the database and cannot be retrieved later.

### Step 5 — Build frontend assets

```bash
npm run build
```

### Step 6 — Storage permissions (Linux/production only)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 7 — Configure CORS

Open `config/cors.php` and add every client app domain that will communicate with this server:

```php
'allowed_origins' => [
    'https://app1.yourdomain.com',
    'https://app2.yourdomain.com',
],
```

### Step 8 — Cache and verify

```bash
php artisan optimize        # cache config, routes, views
php artisan route:list      # should show 47 routes
```

### Step 9 — Web Server (Nginx example)

```nginx
server {
    listen 443 ssl;
    server_name auth.yourdomain.com;
    root /var/www/rajin-auth/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Local development

```bash
php artisan serve
```

---

## Registering a Client App

Every app that uses this service must be registered as an OAuth client.

**Confidential client** (server-side app — has a backend that can store a secret):

```bash
php artisan passport:client \
  --name="My App" \
  --redirect_uri="https://myapp.yourdomain.com/auth/callback"
```

**Public client** (SPA or mobile app — no secret, uses PKCE):

```bash
php artisan passport:client \
  --name="My SPA" \
  --public \
  --redirect_uri="https://spa.yourdomain.com/callback"
```

You can also register clients via the admin panel at `/admin/clients`.

Store the output credentials in your client app's `.env`:

```ini
AUTH_BASE_URL=https://auth.yourdomain.com
AUTH_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
AUTH_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx  # omit for public clients
AUTH_REDIRECT_URI=https://myapp.yourdomain.com/auth/callback
```

---

## Client Integration Guide

### How it works

```
1. User clicks "Login" on your app
2. Your app redirects the browser to /oauth/authorize on this server
3. User logs in (email/password or Google) and approves the consent screen
4. This server redirects back to your app with an authorization code
5. Your app exchanges the code for an access token (server-to-server, never in browser)
6. Your app calls /api/userinfo with the token to get the user's identity
7. Your app creates or updates its local user record using the stable `sub` ID
```

> Always identify users by `sub` (stable numeric ID) — never by email. Email can change; `sub` never does.

---

### Step 1 — Redirect the user to this server

Build the authorization URL and redirect the browser to it. Generate a random `state` value and store it in the session — you'll verify it in the callback to prevent CSRF.

```
GET https://auth.yourdomain.com/oauth/authorize
    ?client_id=YOUR-UUID
    &redirect_uri=https://myapp.yourdomain.com/auth/callback
    &response_type=code
    &scope=openid profile email offline_access
    &state=RANDOM_STRING
```

**Scopes:**

| Scope | What it returns |
|---|---|
| `openid` | `sub` (always included) |
| `profile` | `name`, `picture` |
| `email` | `email`, `email_verified` |
| `offline_access` | Enables refresh tokens |

---

### Step 2 — Handle the callback

Your app receives:

```
GET https://myapp.yourdomain.com/auth/callback?code=ABC&state=XYZ
```

1. Verify `state` matches the value stored in the session — abort if it doesn't
2. Exchange the `code` for tokens via a **server-to-server POST** (the browser must never see this request):

```
POST https://auth.yourdomain.com/oauth/token
Content-Type: application/json

{
  "grant_type":    "authorization_code",
  "client_id":     "YOUR-UUID",
  "client_secret": "YOUR-SECRET",
  "redirect_uri":  "https://myapp.yourdomain.com/auth/callback",
  "code":          "ABC"
}
```

Response:

```json
{
  "access_token":  "eyJ...",
  "token_type":    "Bearer",
  "expires_in":    86400,
  "refresh_token": "def...",
  "scope":         "openid profile email offline_access"
}
```

---

### Step 3 — Get the user's identity

```
GET https://auth.yourdomain.com/api/userinfo
Authorization: Bearer eyJ...
```

Response:

```json
{
  "sub":            "42",
  "name":           "Jane Doe",
  "email":          "jane@example.com",
  "picture":        "https://lh3.googleusercontent.com/...",
  "email_verified": true
}
```

Use `sub` as the key to find or create the user in your own database.

---

### Step 4 — Refresh an expired token

Access tokens expire after 24 hours. Use the refresh token to get a new pair silently:

```
POST https://auth.yourdomain.com/oauth/token

{
  "grant_type":    "refresh_token",
  "refresh_token": "def...",
  "client_id":     "YOUR-UUID",
  "client_secret": "YOUR-SECRET",
  "scope":         "openid profile email offline_access"
}
```

Returns a new `access_token` and `refresh_token`. The old refresh token is immediately invalidated. Refresh tokens expire after 30 days.

---

### Code examples

#### Laravel (PHP)

```php
// routes/web.php

Route::get('/auth/redirect', function () {
    $state = Str::random(40);
    session(['oauth_state' => $state]);

    $query = http_build_query([
        'client_id'     => config('services.auth.client_id'),
        'redirect_uri'  => config('services.auth.redirect'),
        'response_type' => 'code',
        'scope'         => 'openid profile email offline_access',
        'state'         => $state,
    ]);

    return redirect(config('services.auth.base_url') . '/oauth/authorize?' . $query);
});

Route::get('/auth/callback', function (Request $request) {
    abort_if($request->state !== session('oauth_state'), 403, 'Invalid state');

    $tokens = Http::post(config('services.auth.base_url') . '/oauth/token', [
        'grant_type'    => 'authorization_code',
        'client_id'     => config('services.auth.client_id'),
        'client_secret' => config('services.auth.client_secret'),
        'redirect_uri'  => config('services.auth.redirect'),
        'code'          => $request->code,
    ])->throw()->json();

    $userInfo = Http::withToken($tokens['access_token'])
        ->get(config('services.auth.base_url') . '/api/userinfo')
        ->throw()->json();

    $user = \App\Models\User::updateOrCreate(
        ['auth_sub' => $userInfo['sub']],
        ['name' => $userInfo['name'], 'email' => $userInfo['email']]
    );

    Auth::login($user);
    return redirect('/dashboard');
});
```

#### Node.js / Express

```js
const axios = require('axios');
const crypto = require('crypto');

app.get('/auth/redirect', (req, res) => {
    const state = crypto.randomBytes(20).toString('hex');
    req.session.oauthState = state;

    const params = new URLSearchParams({
        client_id:     process.env.AUTH_CLIENT_ID,
        redirect_uri:  process.env.AUTH_REDIRECT_URI,
        response_type: 'code',
        scope:         'openid profile email offline_access',
        state,
    });

    res.redirect(`${process.env.AUTH_BASE_URL}/oauth/authorize?${params}`);
});

app.get('/auth/callback', async (req, res) => {
    if (req.query.state !== req.session.oauthState) {
        return res.status(403).send('Invalid state');
    }

    const { data: tokens } = await axios.post(
        `${process.env.AUTH_BASE_URL}/oauth/token`,
        {
            grant_type:    'authorization_code',
            client_id:     process.env.AUTH_CLIENT_ID,
            client_secret: process.env.AUTH_CLIENT_SECRET,
            redirect_uri:  process.env.AUTH_REDIRECT_URI,
            code:          req.query.code,
        }
    );

    const { data: user } = await axios.get(
        `${process.env.AUTH_BASE_URL}/api/userinfo`,
        { headers: { Authorization: `Bearer ${tokens.access_token}` } }
    );

    req.session.user = user;
    req.session.accessToken = tokens.access_token;
    req.session.refreshToken = tokens.refresh_token;
    res.redirect('/dashboard');
});
```

#### Python / FastAPI

```python
import os, secrets
from urllib.parse import urlencode
import httpx
from fastapi import FastAPI, Request
from fastapi.responses import RedirectResponse

app = FastAPI()
AUTH_BASE = os.getenv("AUTH_BASE_URL")

@app.get("/auth/redirect")
def auth_redirect(request: Request):
    state = secrets.token_hex(20)
    request.session["oauth_state"] = state

    params = urlencode({
        "client_id":     os.getenv("AUTH_CLIENT_ID"),
        "redirect_uri":  os.getenv("AUTH_REDIRECT_URI"),
        "response_type": "code",
        "scope":         "openid profile email offline_access",
        "state":         state,
    })
    return RedirectResponse(f"{AUTH_BASE}/oauth/authorize?{params}")

@app.get("/auth/callback")
async def auth_callback(request: Request, code: str, state: str):
    if state != request.session.get("oauth_state"):
        return {"error": "Invalid state"}, 403

    async with httpx.AsyncClient() as client:
        token_resp = await client.post(f"{AUTH_BASE}/oauth/token", json={
            "grant_type":    "authorization_code",
            "client_id":     os.getenv("AUTH_CLIENT_ID"),
            "client_secret": os.getenv("AUTH_CLIENT_SECRET"),
            "redirect_uri":  os.getenv("AUTH_REDIRECT_URI"),
            "code":          code,
        })
        tokens = token_resp.json()

        user_resp = await client.get(f"{AUTH_BASE}/api/userinfo", headers={
            "Authorization": f"Bearer {tokens['access_token']}"
        })
        user = user_resp.json()

    request.session["user"] = user
    request.session["access_token"] = tokens["access_token"]
    request.session["refresh_token"] = tokens.get("refresh_token")
    return RedirectResponse("/dashboard")
```

---

## Key Endpoints

| Endpoint | Method | Description |
|---|---|---|
| `/oauth/authorize` | GET | Start an authorization flow |
| `/oauth/token` | POST | Exchange code for tokens / refresh |
| `/api/userinfo` | GET | Get identity claims for a Bearer token |
| `/api/user` | GET | Alias for `/api/userinfo` |
| `/auth/google/redirect` | GET | Initiate Google login |
| `/admin/clients` | GET | List registered OAuth clients |
| `/admin/clients/create` | GET | Register a new client |
| `/admin/users` | GET | View registered users |

---

## Running Tests

```bash
php artisan test
```

25 tests, 61 assertions — all should pass.

---

## Security

- Rate limiting: login/register/forgot-password (10 req/min), token endpoint (30 req/min)
- Explicit CORS allowlist in `config/cors.php` — no wildcard origins
- Security headers on all responses (X-Frame-Options, X-Content-Type-Options, etc.)
- HTTPS forced in production (`APP_ENV=production`)
- UUID client IDs (non-guessable)
- Passwords nullable — Google-only users cannot log in with email + password
- State parameter validation required on all clients (CSRF protection)

---

## License

MIT
