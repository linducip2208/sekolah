<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Priority: ?lang=xx → session → user.locale → default
        $locale = $request->query('lang');
        if ($locale) {
            session(['locale' => $locale]);
        }
        $locale = $locale
            ?? session('locale')
            ?? auth()->user()?->locale
            ?? config('app.locale', 'id');

        if (in_array($locale, ['id', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
