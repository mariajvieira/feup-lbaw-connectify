<?php
 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            session(['id' => Auth::id()]);     
            session()->save();     

            // $user = Auth::user();
            // \DB::statement("SET app.current_user = ?", [Auth::user()->username]);
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

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'This email is not registered.']);
        }
        
        // Gera um código de recuperação (número aleatório de 6 dígitos)
        $code = mt_rand(100000, 999999);
        
        // Salva o código e o e-mail na sessão
        session(['reset_code' => $code, 'reset_email' => $email]);
        
        // Envia o código por e-mail usando Mailtrap
        Mail::raw("Dear $email,\n\nWe have received your request to reset your password for your Connectify account. Your password recovery code is: $code.\n\nIf you did not initiate this request, please disregard this email.\n\nBest regards,\nThe Connectify Support Team", function ($message) use ($email) {
            $message->to($email, 'Connectify User')
                    ->from('support@connectify.com', 'Connectify Support')
                    ->subject('Password Recovery Code');
        });
        
        
        return redirect()->route('verifyCodePage');
    }
    

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
        ]);
    
        $email = $request->input('email');
        $code = $request->input('code');
    
        if (session('reset_email') === $email && session('reset_code') == $code) {
            // Code is valid; render the reset password form
            return redirect()->route('resetPasswordPage')->with(['email' => $email]);
        }
    
        // Code is invalid; redirect back with an error
        return back()->withErrors(['code' => 'Invalid or expired code.']);
    }
    
    

    public function verifyCodePage()
    {
        return view('auth.verifyCode');
    }
    

    public function forgotPassword(Request $request) {
        return view('auth.forgotPassword');
    }

    public function resetPasswordPage(Request $request)
    {
        if (!session('reset_email')) {
            return redirect()->route('forgotPassword')->withErrors(['error' => 'Invalid session data.']);
        }

        return view('auth.resetPassword', [
            'email' => session('reset_email'),
        ]);
    }



    public function resetPassword(Request $request)
    {

        if (Auth::check()) {
            DB::statement("SET app.current_user = ?", [Auth::user()->username]);
        }
        // Valida os dados do formulário
        $request->validate([
            'email' => 'required|email|exists:users,email', // Verifica se o email existe na base de dados
            'password' => 'required|min:8|confirmed', // Valida a nova senha
        ]);
    
        // Obtém o email e a nova senha do formulário
        $email = $request->input('email');
        $newPassword = $request->input('password');
    
        // Atualiza a senha do usuário
        $user = User::where('email', $email)->first();
        
        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
            
            // Remove os dados da sessão
            session()->forget(['reset_code', 'reset_email']);
            
            return redirect()->route('login')->with('status', 'Password reset successfully!');
        }
    
        return redirect()->route('forgotPassword')->with('error', 'Email not found.');
    }

    

}

