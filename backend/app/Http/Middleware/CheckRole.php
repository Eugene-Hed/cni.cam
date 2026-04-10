<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Accepted role IDs (e.g. "1", "2", "3", "4")
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        if (!in_array((string) $user->RoleId, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé pour votre rôle.',
            ], 403);
        }

        return $next($request);
    }
}
