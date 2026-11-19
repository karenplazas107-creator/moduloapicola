<?php

namespace Modules\Evidencias\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EsGestorEvidencias
{
    /**
     * ID del único usuario autorizado para gestionar este panel.
     */
    private const GESTOR_ID = 124;

    /**
     * Maneja la solicitud entrante.
     * Permite el acceso solo si el usuario está autenticado
     * y su ID coincide con el gestor de evidencias.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Debes iniciar sesión para acceder a este panel.');
        }

        // Verificar que sea el gestor autorizado
        if (Auth::id() !== self::GESTOR_ID) {
            abort(403, 'No tienes permiso para acceder al panel de evidencias.');
        }

        return $next($request);
    }
}
