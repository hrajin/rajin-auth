<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Token;

class DeviceSession extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'token_id',
        'device_fingerprint',
        'user_agent',
        'ip_address',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The current active Passport access token for this device session.
     */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    /**
     * Scope: only sessions whose token is still valid (not revoked, not expired).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('accessToken', fn ($q) =>
            $q->where('revoked', false)
              ->where('expires_at', '>', now())
        );
    }
}
