<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotCustomerAccessOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isCustomerAccessOnly()) {
            abort(403, 'Akun ini hanya bisa dipakai untuk data pelanggan.');
        }

        return $next($request);
    }
}
