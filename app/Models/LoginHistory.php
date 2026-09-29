<?php

namespace App\Models;

use App\Services\UserAgentParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'status',
        'failure_reason',
        'login_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccess(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Record a successful login event.
     */
    public static function recordSuccess(User $user, ?Request $request = null): self
    {
        $req = $request ?? request();
        $userAgent = $req->userAgent();

        return static::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $req->ip() ?? '127.0.0.1',
            'user_agent' => $userAgent,
            'device_type' => UserAgentParser::getDeviceType($userAgent),
            'browser' => UserAgentParser::getBrowser($userAgent),
            'platform' => UserAgentParser::getPlatform($userAgent),
            'status' => 'success',
            'failure_reason' => null,
            'login_at' => now(),
        ]);
    }

    /**
     * Record a failed login event.
     */
    public static function recordFailure(string $email, ?string $reason = null, ?Request $request = null, ?User $user = null): self
    {
        $req = $request ?? request();
        $userAgent = $req->userAgent();

        return static::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $req->ip() ?? '127.0.0.1',
            'user_agent' => $userAgent,
            'device_type' => UserAgentParser::getDeviceType($userAgent),
            'browser' => UserAgentParser::getBrowser($userAgent),
            'platform' => UserAgentParser::getPlatform($userAgent),
            'status' => 'failed',
            'failure_reason' => $reason ?? 'Invalid credentials',
            'login_at' => now(),
        ]);
    }
}
