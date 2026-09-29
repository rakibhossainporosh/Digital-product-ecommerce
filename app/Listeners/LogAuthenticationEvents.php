<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;

class LogAuthenticationEvents
{
    public function __construct(
        protected Request $request
    ) {}

    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            LoginHistory::recordSuccess($event->user, $this->request);
        }
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? $this->request->input('email', 'unknown');
        $user = $event->user instanceof User ? $event->user : User::where('email', $email)->first();

        LoginHistory::recordFailure(
            email: (string) $email,
            reason: 'Invalid credentials',
            request: $this->request,
            user: $user
        );
    }

    public function handleLockout(Lockout $event): void
    {
        $email = $this->request->input('email', 'unknown');
        $user = User::where('email', $email)->first();

        LoginHistory::recordFailure(
            email: (string) $email,
            reason: 'Account locked out due to excessive login attempts',
            request: $this->request,
            user: $user
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }
}
