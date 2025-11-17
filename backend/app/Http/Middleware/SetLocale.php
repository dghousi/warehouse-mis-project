<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

final class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('NEXT_LOCALE')
            ?? $request->user()?->locale
            ?? $request->header('Accept-Language', config('app.fallback_locale'));

        if (in_array($locale, config('app.available_locales'))) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.fallback_locale'));
        }

        return $next($request);
    }
}
