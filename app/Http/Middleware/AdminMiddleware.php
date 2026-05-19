<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FirebaseHelper;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uid = session('user.uid');

        if (!$uid) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = FirebaseHelper::baca("users/{$uid}");

        if (!$user || ($user['role'] ?? 'user') !== 'admin') {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin.');
        }

        return $next($request);
    }
}
