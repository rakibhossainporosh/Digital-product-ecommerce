<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always pass health check and admin/filament routes
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $isMaintenance = (bool) setting('maintenance_mode', false);

        if (! $isMaintenance) {
            return $next($request);
        }

        // Secret bypass key verification
        $bypassKey = (string) setting('maintenance_bypass_key', '');

        if ($bypassKey !== '') {
            if ($request->query('bypass_key') === $bypassKey) {
                $response = $next($request);

                return $response->withCookie(cookie(
                    name: 'maintenance_bypass_token',
                    value: $bypassKey,
                    minutes: 60 * 24, // 24 hours
                    httpOnly: true
                ));
            }

            if ($request->cookie('maintenance_bypass_token') === $bypassKey) {
                return $next($request);
            }
        }

        $headline = (string) setting('maintenance_headline', 'Under Scheduled Maintenance');
        $message = (string) setting('maintenance_message', 'Our system is currently undergoing scheduled maintenance and upgrades. We will be back shortly.');
        $supportEmail = (string) setting('support_email', '');
        $supportWhatsapp = (string) setting('support_whatsapp', '');
        $appName = (string) setting('app_name', config('app.name', 'Digital Product Store'));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'maintenance',
                'headline' => $headline,
                'message' => $message,
                'support_email' => $supportEmail,
                'support_whatsapp' => $supportWhatsapp,
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if (view()->exists('errors.503')) {
            return response()->view('errors.503', [
                'headline' => $headline,
                'message' => $message,
                'appName' => $appName,
                'supportEmail' => $supportEmail,
                'supportWhatsapp' => $supportWhatsapp,
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response(
            <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{$headline} - {$appName}</title>
                <style>
                    body { font-family: ui-sans-serif, system-ui, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
                    .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 40px; max-width: 520px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
                    .badge { display: inline-block; background: #f59e0b1a; color: #f59e0b; padding: 4px 12px; border-radius: 9999px; font-size: 13px; font-weight: 600; margin-bottom: 16px; border: 1px solid #f59e0b33; }
                    h1 { margin: 0 0 12px; font-size: 24px; font-weight: 700; color: #fff; }
                    p { color: #94a3b8; font-size: 15px; line-height: 1.6; margin: 0 0 24px; }
                    .support { font-size: 14px; color: #64748b; border-top: 1px solid #334155; padding-top: 20px; }
                    .support a { color: #38bdf8; text-decoration: none; }
                </style>
            </head>
            <body>
                <div class="card">
                    <span class="badge">System Maintenance</span>
                    <h1>{$headline}</h1>
                    <p>{$message}</p>
                    <div class="support">
                        Need urgent help? Contact us via
                        <a href="mailto:{$supportEmail}">{$supportEmail}</a>
                    </div>
                </div>
            </body>
            </html>
            HTML,
            Response::HTTP_SERVICE_UNAVAILABLE
        );
    }

    /**
     * Determine if the request should bypass maintenance mode checks.
     */
    protected function shouldPassThrough(Request $request): bool
    {
        return $request->is('admin*')
            || $request->is('livewire*')
            || $request->is('up')
            || $request->is('_debugbar*');
    }
}
