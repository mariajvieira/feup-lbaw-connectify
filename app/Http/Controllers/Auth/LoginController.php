<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        if (Auth::check()) {
            return redirect('/posts');
        } else {
            return view('auth.login');
        }
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required'],
            'user_password' => ['required'], // Correção para `user_password`
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginField => $credentials['login'], 'user_password' => $credentials['user_password']], $request->filled('remember'))) { // Corrigido `credentials['password']`
            $request->session()->regenerate();
            return redirect()->intended('/posts'); 
        }

        return back()->withErrors([
            'login' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withSuccess('You have logged out successfully!');
    }
}
