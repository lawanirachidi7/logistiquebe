<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanActionMiddleware
{
    /**
     * Handle an incoming request.
     * Seuls les admins et opérateurs peuvent effectuer des actions.
     * Les managers ont un accès en consultation uniquement.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->canPerformActions()) {
            // Pour les requêtes AJAX, retourner une erreur JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Action non autorisée. Votre rôle est en consultation uniquement.'
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Action non autorisée. Votre rôle est en consultation uniquement.');
        }

        return $next($request);
    }
}
