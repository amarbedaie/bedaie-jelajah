<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kawalan capaian berasaskan peranan.
 *
 * Digunakan sebagai `role:admin` atau `role:penggerak,admin`.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        // Pasukan BeDaie sentiasa boleh masuk ke ruang operasi.
        if ($user->isAdmin()) {
            return $next($request);
        }

        abort_unless(in_array($user->role->value, $roles, true), 403,
            'Anda tiada kebenaran untuk mengakses ruang ini.');

        return $next($request);
    }
}
