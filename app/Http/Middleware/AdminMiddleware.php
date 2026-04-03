<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent accéder à cette section.');
        }

        return $next($request);
    }
}
