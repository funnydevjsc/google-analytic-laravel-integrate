<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\GoogleTagManager\GoogleTagManager;

class GoogleAnalyticMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            app(GoogleTagManager::class)->set('client_id', Auth::user()->uuid);
            $param = [
                'user_id' => Auth::user()->uuid,
                'sha256_email_address' => hash('sha256', Auth::user()->email)
            ];
            if (Auth::user()->phone_number) {
                $param['sha256_phone_number'] = hash('sha256', Auth::user()->phone_number);
            }
            if (Auth::user()->first_name) {
                $param['address.sha256_first_name'] = hash('sha256', Auth::user()->first_name);
            }
            if (Auth::user()->last_name) {
                $param['address.sha256_last_name'] = hash('sha256', Auth::user()->last_name);
            }
            if (Auth::user()->region) {
                $param['address.region'] = Auth::user()->region;
            }
            if (Auth::user()->country) {
                $param['address.country'] = Auth::user()->country;
            }
            app(GoogleTagManager::class)->set('user_data', $param);
        }

        return $next($request);
    }
}
