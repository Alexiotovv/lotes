<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class VendedorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (in_array(Auth::user()->role, ['vendedor', 'admin'])) {
            return $next($request);
        }

        // Si no es vendedor, redirigir o denegar acceso
        abort(403, 'Acceso no autorizado.');
    }
}