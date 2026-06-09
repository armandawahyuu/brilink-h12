<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLoginDev
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('local') && !Auth::check()) {
            $user = User::first();
            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
