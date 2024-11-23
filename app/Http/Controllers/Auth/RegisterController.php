<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Display a registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        // Validação personalizada para que o campo 'password' seja validado com 'password_confirmation'
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:8'
        ]);

        // Validar o campo 'password' com o campo 'password_confirmation'
        $validator->after(function ($validator) use ($request) {
            if ($request->password !== $request->password_confirmation) {
                $validator->errors()->add('password_confirmation', 'As senhas não coincidem.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Criação do usuário
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),  // Criptografar o campo 'password'
            ]);
        } catch (\Exception $e) {
            // Tratar erro de criação do usuário
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Autenticação do usuário após o registro
        Auth::login($user); // Usar o usuário recém-criado
        $request->session()->regenerate();

        // Redirecionar para a página inicial com uma mensagem de sucesso
        return redirect()->route('home')
            ->withSuccess('Você se registrou e fez login com sucesso!');
    }
}
