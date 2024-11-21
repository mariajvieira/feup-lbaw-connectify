<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Display the registration form.
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
        // Validação dos dados enviados pelo formulário
        $request->validate([
            'username' => 'required|string|max:250|unique:users,username',
            'email' => 'required|email|max:250|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_public' => 'sometimes|boolean', // Caso não enviado, assume o default do banco
        ]);

        // Define a imagem de perfil padrão
        $profilePicturePath = 'images/default.png'; // Caminho relativo no sistema público

        // Salva a imagem de perfil se foi enviada
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Criação do novo usuário
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'profile_picture' => $profilePicturePath,
            'is_public' => $request->has('is_public') ? $request->is_public : true, // Se não enviado, assume público
            'password' => Hash::make($request->password),
        ]);

        // Autentica o novo usuário
        Auth::login($user);
        $request->session()->regenerate();

        // Redireciona após o registro e login
        return redirect()->route('posts')
            ->with('success', 'You have successfully registered & logged in!');
    }
}
