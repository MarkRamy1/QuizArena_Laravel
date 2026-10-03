<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (!$locale && Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
        }

        if (!$locale || !in_array($locale, ['ar', 'en'])) {
            $locale = config('app.locale', 'ar');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
