<?php

 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            'password' => ['required'],
        ]);
        
        // Mapeia o campo 'password' para 'password'
        $credentials['password'] = $credentials['password'];
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            session(['id' => Auth::id()]);     
            session()->save();     
            return redirect('/home');

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
        return redirect()->route('welcome')->withSuccess('You have logged out successfully!');
    }


    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    
        $email = $request->input('email');
    
        // Gera um código numérico de 6 dígitos
        $code = mt_rand(100000, 999999);
    
        // Guarda o código e o email na sessão (para validação posterior)
        session(['reset_code' => $code, 'reset_email' => $email]);
    
        // Envia o código por email
        Mail::raw("O seu código de recuperação de palavra-passe é: $code", function ($message) use ($email) {
            $message->to($email, 'Connectify User')
                    ->from('connectify@example.com', 'Connectify')
                    ->subject('Token for password recovery');
        });
    
        return response()->json(['message' => 'Token sen successfully! Check your inbox.']);
    }


    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|min:8|confirmed',
        ]);
    
        $email = $request->input('email');
        $code = $request->input('code');
        $newPassword = $request->input('password');
    
        // Verifica se o código e o email coincidem com os armazenados na sessão
        if (session('reset_email') === $email && session('reset_code') == $code) {
            // Atualiza a palavra-passe do utilizador
            User::where('email', $email)->update(['password' => Hash::make($newPassword)]);
    
            // Remove os dados da sessão
            session()->forget(['reset_code', 'reset_email']);
    
            return response()->json(['message' => 'Password reset successfully!']);
        }
    
        return response()->json(['error' => 'Invalid or expired code.'], 400);
    }    

    public function forgotPassword(Request $request) {
        return view('auth.forgotPassword');
    }

}

