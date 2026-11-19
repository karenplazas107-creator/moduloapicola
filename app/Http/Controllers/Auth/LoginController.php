<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión del ERP.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $redirect = $request->query('redirect', '');
            if (!empty($redirect)) {
                return redirect($redirect)->with('info', 'Ya has iniciado sesión como ' . $user->full_name);
            }

            if ($user->roles->contains('slug', 'apicola.admin') || $user->hasSuperAdmin()) {
                return redirect()->route('apicola.admin.dashboard')
                    ->with('info', 'Ya has iniciado sesión como ' . $user->full_name);
            }

            if ($user->roles->contains('slug', 'apicola.aprendiz')) {
                return redirect()->route('apicola.aprendiz.dashboard')
                    ->with('info', 'Ya has iniciado sesión como ' . $user->full_name);
            }

            return redirect(route('direccion.welcome'))
                ->with('info', 'Ya has iniciado sesión como ' . $user->full_name);
        }

        $redirect = $request->query('redirect', '');
        return view('login', compact('redirect'));
    }

    /**
     * Procesa la autenticación del usuario en el ERP.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Debes ingresar tu correo institucional o usuario.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ]);

        $loginInput = trim($request->input('email'));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Buscar al usuario por correo o por nickname
        $user = User::where('email', $loginInput)
            ->orWhere('nickname', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            $redirectUrl = $request->input('redirect');
            if (!empty($redirectUrl) && (str_starts_with($redirectUrl, '/') || str_starts_with($redirectUrl, url('/')))) {
                return redirect($redirectUrl)->with('success', '¡Bienvenido(a) a SENA Empresa, ' . $user->full_name . '!');
            }

            // Redirigir según el rol del módulo
            if ($user->roles->contains('slug', 'apicola.admin') || $user->hasSuperAdmin()) {
                return redirect()->route('apicola.admin.dashboard')
                    ->with('success', '¡Bienvenido(a) al Panel Apícola, ' . $user->full_name . '!');
            }

            if ($user->roles->contains('slug', 'apicola.aprendiz')) {
                return redirect()->route('apicola.aprendiz.dashboard')
                    ->with('success', '¡Bienvenido(a) al Panel Apícola, ' . $user->full_name . '!');
            }

            return redirect()->intended(route('direccion.welcome'))->with('success', '¡Bienvenido(a) a SENA Empresa, ' . $user->full_name . '!');
        }

        return back()
            ->withInput($request->only('email', 'remember', 'redirect'))
            ->withErrors([
                'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
            ]);
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logout(Request $request)
    {
        $userName = Auth::check() ? Auth::user()->full_name : 'Usuario';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirectUrl = $request->input('redirect', $request->query('redirect', ''));
        if (!empty($redirectUrl) && (str_starts_with($redirectUrl, '/') || str_starts_with($redirectUrl, url('/')))) {
            return redirect($redirectUrl)->with('info', 'Has cerrado sesión exitosamente.');
        }

        return redirect()->route('direccion.welcome')->with('info', 'Has cerrado sesión exitosamente. ¡Hasta pronto, ' . $userName . '!');
    }
}
