<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->is_blocked) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Vaš nalog je blokiran. Kontaktirajte administratora.']);
        }

        return $next($request);
    }
}
