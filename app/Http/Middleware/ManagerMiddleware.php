<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->canAccessSettings()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs et managers peuvent accéder à cette section.');
        }

        return $next($request);
    }
}
