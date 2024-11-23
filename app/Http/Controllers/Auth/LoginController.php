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
    public function authenticate(Request $request)
    {
        // Valida os dados do formulário
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'user_password' => ['required'],
        ]);
        
        // Mapeia o campo 'user_password' para 'password'
        $credentials['password'] = $credentials['user_password'];
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

