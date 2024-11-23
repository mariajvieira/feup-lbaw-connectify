<?php

 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\View\View;

class LoginController extends Controller
{

    /**
     * Display a login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/home');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'user_password' => ['required'],
        ]);
 
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            session(['id' => Auth::id()]);
            session()->save();
 
            return redirect()->intended('/home');
        }
 
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log out the user from application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->withSuccess('You have logged out successfully!');
    } 
}


/*
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;

class LoginController extends Controller
{


    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/cards');
        } else {
            return view('auth.login');
        }
    }


    public function authenticate(Request $request): RedirectResponse
    {
        // Valida os dados recebidos
        $credentials = $request->validate([
            'username' => ['required'],
            'user_password' => ['required'],
        ]);

        // Buscar o usuário com base no username
        $user = User::where('username', $credentials['username'])->first();

        // Verifica se o usuário existe e se a senha fornecida corresponde ao hash armazenado
        if ($user && Hash::check($credentials['user_password'], $user->user_password)) {
            // Se as credenciais estiverem corretas, realiza o login
            Auth::login($user);
            $request->session()->regenerate();

            // Redireciona após o login bem-sucedido
            return redirect()->intended('/home');
        }

        // Se as credenciais estiverem erradas, retorna com um erro
        return back()->withErrors([
            'username' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('username');
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->withSuccess('You have logged out successfully!');
    }
}
    */
