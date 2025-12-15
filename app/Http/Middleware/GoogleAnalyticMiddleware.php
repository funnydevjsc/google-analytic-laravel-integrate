<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\GoogleTagManager\GoogleTagManager;

class GoogleAnalyticMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->uuid ?? $user->id ?? null;
            if ($userId !== null) {
                $userId = (string) $userId;
            }

            // GA4: use user_id (stable, non-PII) for cross-device reporting/audiences.
            if (!empty($userId)) {
                app(GoogleTagManager::class)->set('ga_user_id', $userId);
                Session::put('gaUserId', $userId);
            }

            // GA4: user_properties should be non-PII. Provide via config mapping/callback.
            $userProperties = \FunnyDev\GoogleAnalytic\GoogleAnalyticSdk::resolveUserPropertiesFromRequest();
            if (!empty($userProperties)) {
                app(GoogleTagManager::class)->set('ga_user_properties', $userProperties);
                Session::put('gaUserProperties', $userProperties);
            }
        }

        return $next($request);
    }
}
