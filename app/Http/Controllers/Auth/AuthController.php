<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Procesar el login
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (!$request->filled('email') && !$request->filled('telefono')) {
            return back()->withErrors(['credentials' => 'Ingresa tu correo o número de teléfono.'])->withInput();
        }

        $remember = $request->boolean('remember');

        // El teléfono se almacena como email en users — funciona el mismo Auth::attempt
        $loginValue = $request->filled('telefono') ? $request->telefono : $request->email;

        if (Auth::attempt(['email' => $loginValue, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();

            // Redirigir según el rol del usuario
            $user = Auth::user();
            
            if ($user->hasRole(['admin', 'encargado'])) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->hasRole(['secretario', 'cajero'])) {
                return redirect()->intended('/admin/citas');
            } elseif ($user->hasRole('finanzas')) {
                return redirect()->intended('/admin/comisiones');
            } elseif ($user->hasRole('estilista')) {
                return redirect()->intended('/admin/mis-citas');
            } else {
                return redirect()->intended('/mi-cuenta');
            }
        }

        $errorField = $request->filled('telefono') ? 'telefono' : 'email';
        return back()->withErrors([
            $errorField => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
