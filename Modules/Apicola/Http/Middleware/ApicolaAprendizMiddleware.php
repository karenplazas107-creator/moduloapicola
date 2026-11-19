<?php

namespace Modules\Apicola\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApicolaAprendizMiddleware
{
    /**
     * Verifica que el usuario autenticado tenga acceso al panel de Aprendiz del módulo Apícola.
     * Permite acceso al Aprendiz (apicola.aprendiz), Apicultor (apicola.admin) y Superadmin.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login', ['redirect' => $request->fullUrl()]));
        }

        $user = Auth::user();

        // Validar si posee el rol de aprendiz, admin o superadministrador
        $hasAprendizRole = $user->roles->contains('slug', 'apicola.aprendiz') || $user->hasRole('apicola.aprendiz');
        $hasAdminRole = $user->roles->contains('slug', 'apicola.admin') || $user->hasRole('apicola.admin');

        if (!$hasAprendizRole && !$hasAdminRole && !$user->hasSuperAdmin()) {
            abort(403, 'Acceso denegado: Se requiere el rol de Aprendiz.');
        }

        return $next($request);
    }
}
