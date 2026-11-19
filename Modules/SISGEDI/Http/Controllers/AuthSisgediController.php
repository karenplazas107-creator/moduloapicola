<?php

namespace Modules\SISGEDI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthSisgediController extends Controller
{
    /**
     * Procesa el inicio de sesión contra users_sisgedi.
     * La tabla almacena la contraseña en texto plano (varchar 50),
     * por lo que se compara directamente sin bcrypt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'nickname'  => 'required|string',
            'password'  => 'required|string',
        ], [
            'nickname.required' => 'Debes ingresar tu correo institucional o usuario.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ]);

        $credencial = trim($request->nickname);
        $password   = $request->password;

        // Buscar por nombre de usuario O por correo
        $usuario = DB::table('users_sisgedi')
            ->where('nombre', $credencial)
            ->orWhere('correo', $credencial)
            ->first();

        if (! $usuario) {
            return back()
                ->withInput($request->only('nickname'))
                ->withErrors(['nickname' => 'El usuario o correo no está registrado en SISGEDI.']);
        }

        // Comparación directa (la tabla usa varchar sin hash)
        if ($usuario->contraseña !== $password) {
            return back()
                ->withInput($request->only('nickname'))
                ->withErrors(['password' => 'Contraseña incorrecta.']);
        }

        // Obtener el nombre del rol
        $rol = DB::table('roles_sisgedi')
            ->where('id_rol', $usuario->id_rol)
            ->value('nombre');

        // Guardar sesión propia de SISGEDI (NO toca Auth de Laravel)
        Session::put('sisgedi_user', [
            'id'     => $usuario->id_users,
            'nombre' => $usuario->nombre,
            'correo' => $usuario->correo,
            'rol'    => $rol ?? 'Sin rol',
            'id_rol' => $usuario->id_rol,
        ]);

        return redirect()->route('sisgedi.dashboard')
            ->with('success', '¡Bienvenido, ' . $usuario->nombre . '!');
    }

    /**
     * Cierra la sesión de SISGEDI.
     */
    public function logout(Request $request)
    {
        Session::forget('sisgedi_user');

        return redirect()->route('sisgedi.index')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}
