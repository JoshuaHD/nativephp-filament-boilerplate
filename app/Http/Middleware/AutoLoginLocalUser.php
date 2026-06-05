<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginLocalUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isLocal()) {
            return $next($request);
        }

        $user = User::firstOrCreate(
            ['email' => 'owner@local'],
            [
                'name' => 'Owner',
                'password' => Hash::make('0000'),
            ]
        );

        Auth::login($user);

        return $next($request);
    }
}
