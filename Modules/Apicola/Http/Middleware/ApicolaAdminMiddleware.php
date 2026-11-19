<?php

namespace Modules\Apicola\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApicolaAdminMiddleware
{
    /**
     * Verifica que el usuario autenticado tenga permisos para administrar el módulo Apícola.
     * Permite acceso al Apicultor (rol apicola.admin) y al Super Administrador.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login', ['redirect' => $request->fullUrl()]));
        }

        $user = Auth::user();

        // Validar si es superadmin o si posee el rol apicola.admin
        $hasApicolaRole = $user->roles->contains('slug', 'apicola.admin') || $user->hasRole('apicola.admin');

        if (!$hasApicolaRole && !$user->hasSuperAdmin()) {
            abort(403, 'Acceso denegado: Se requiere el rol de Apicultor.');
        }

        return $next($request);
    }
}
