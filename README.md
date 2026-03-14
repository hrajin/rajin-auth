# rajin-auth

A standalone **OAuth 2.0 / OpenID Connect Identity Provider (IdP)** built with Laravel 12 and Passport 12. Acts as a central authentication server for a suite of applications — each app delegates login, profile management, and password handling to this service instead of managing its own users.

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
- Full **OIDC discovery** — client SDKs auto-configure via `/.well-known/openid-configuration`
- **JWKS endpoint** — clients verify JWTs locally using the public key
- Standard **OIDC `/api/userinfo`** endpoint — scope-filtered identity claims
- **Profile API** — client apps can read and update user profile data
- **Password change API** — client apps can offer change password without managing credentials
- **Instagram-style logout** — single session or global logout with back-channel webhooks to all registered apps
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
APP_URL=http://localhost:8000   # match the port you run on locally
APP_ENV=local
APP_DEBUG=true

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
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Token expiry
PASSPORT_TOKEN_EXPIRE_IN=1440           # 24 hours
PASSPORT_REFRESH_TOKEN_EXPIRE_IN=43200  # 30 days
```

> **Important:** `APP_URL` must exactly match the URL you access the app on (including port). A mismatch causes `InvalidStateException` on Google OAuth callbacks.

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
php artisan storage:link
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
php artisan route:list      # verify all routes are registered
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
# Access at http://localhost:8000
```

---

## Registering a Client App

Every app that uses this service must be registered as an OAuth client. Use the admin panel at `/admin/clients` → **New Client**.

Fill in:
- **Application Name** — shown to users on the consent screen
- **Redirect URI** — where Google/token callbacks land
- **Logout URI** *(optional)* — rajin-auth will POST here when a user triggers global logout so your app can destroy its local session
- **Client Type** — Confidential (server-side) or Public (SPA/mobile, uses PKCE)

After creation, copy the credentials into your client app's `.env`:

```ini
RAJIN_AUTH_URL=https://auth.yourdomain.com
RAJIN_AUTH_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
RAJIN_AUTH_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx  # omit for public clients
RAJIN_AUTH_LOGOUT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx  # shown once after creation
```

---

## API Reference

All authenticated endpoints require `Authorization: Bearer <access_token>`.

### Public endpoints (no auth)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/.well-known/openid-configuration` | OIDC discovery document |
| `GET` | `/.well-known/jwks.json` | RSA public key for JWT verification |
| `GET` | `/api/health` | Health check — `{"status":"ok"}` |

### Client-authenticated

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/token/introspect` | client_id + client_secret | Validate a token server-to-server |

### User-authenticated (Bearer token)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/userinfo` | OIDC userinfo — scope-filtered claims |
| `GET` | `/api/profile` | Full profile data |
| `PATCH` | `/api/profile` | Update name, phone, bio, address, etc. |
| `POST` | `/api/profile/avatar` | Upload avatar image (multipart) |
| `POST` | `/api/password/change` | Change password |
| `POST` | `/api/logout` | Logout current session only |
| `POST` | `/api/logout/all` | Logout all devices + back-channel notify all apps |

### OAuth / Passport (built-in)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/oauth/authorize` | Start authorization code flow |
| `POST` | `/oauth/token` | Exchange code for tokens / refresh tokens |
| `POST` | `/oauth/token/revoke` | Revoke a specific token (requires client credentials) |

---

## Client Integration — Quick Start (Laravel)

```php
// config/services.php
'rajin_auth' => [
    'url'            => env('RAJIN_AUTH_URL'),
    'client_id'      => env('RAJIN_AUTH_CLIENT_ID'),
    'redirect_uri'   => env('APP_URL') . '/auth/callback',
    'logout_secret'  => env('RAJIN_AUTH_LOGOUT_SECRET'),
],
```

```php
// Login redirect (with PKCE)
public function redirect(): RedirectResponse
{
    $verifier  = Str::random(64);
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    session(['pkce_verifier' => $verifier]);

    $query = http_build_query([
        'client_id'             => config('services.rajin_auth.client_id'),
        'redirect_uri'          => config('services.rajin_auth.redirect_uri'),
        'response_type'         => 'code',
        'scope'                 => 'openid profile email',
        'code_challenge'        => $challenge,
        'code_challenge_method' => 'S256',
    ]);

    return redirect(config('services.rajin_auth.url') . '/oauth/authorize?' . $query);
}

// Callback — exchange code, fetch profile, create local user
public function callback(Request $request): RedirectResponse
{
    $tokens = Http::post(config('services.rajin_auth.url') . '/oauth/token', [
        'grant_type'    => 'authorization_code',
        'client_id'     => config('services.rajin_auth.client_id'),
        'redirect_uri'  => config('services.rajin_auth.redirect_uri'),
        'code'          => $request->code,
        'code_verifier' => session('pkce_verifier'),
    ])->json();

    $profile = Http::withToken($tokens['access_token'])
        ->get(config('services.rajin_auth.url') . '/api/profile')
        ->json();

    $user = User::updateOrCreate(
        ['auth_sub' => $profile['sub']],
        ['name' => $profile['name'], 'email' => $profile['email']]
    );

    session(['access_token' => $tokens['access_token']]);
    Auth::login($user);

    return redirect('/home');
}

// Back-channel logout receiver
public function backChannelLogout(Request $request): JsonResponse
{
    $payload  = $request->only('sub', 'iat');
    $expected = hash_hmac('sha256', json_encode($payload), config('services.rajin_auth.logout_secret'));

    if (!hash_equals($expected, $request->header('X-Signature', ''))) {
        return response()->json(['error' => 'unauthorized'], 401);
    }

    $user = User::where('auth_sub', $payload['sub'])->first();
    if ($user) {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }

    return response()->json(['ok' => true]);
}
```

> Always identify users by `sub` (stable numeric ID) — never by email. Email can change; `sub` never does.

---

## Running Tests

```bash
php artisan test
```

138 tests, 335 assertions — all should pass.

---

## Security

- Rate limiting: login/register/forgot-password (10 req/min), token endpoint (30 req/min)
- Explicit CORS allowlist in `config/cors.php` — no wildcard origins
- Security headers on all responses (X-Frame-Options, X-Content-Type-Options, etc.)
- HTTPS forced in production (`APP_ENV=production`)
- UUID client IDs (non-guessable)
- Passwords nullable — Google-only users cannot log in with email + password
- Back-channel logout webhooks use HMAC-SHA256 signatures
- `APP_URL` must match actual server URL to prevent `InvalidStateException` on OAuth callbacks

---

## License

MIT
