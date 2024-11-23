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
     * Display a login form.
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
        $request->validate([
            'username' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users',
            'user_password' => 'required|min:8|confirmed'
        ]);

        try {
            User::create([
                'username' => $request->username,
                'email' => $request->email,
                'user_password' => Hash::make($request->user_password),
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $credentials = $request->only('email', 'user_password');

        Auth::attempt($credentials);
        $request->session()->regenerate();
        return redirect()->route('home')
            ->withSuccess('You have successfully registered & logged in!');
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

class RegisterController extends Controller
{

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }


    public function register(Request $request)
    {
        // Validação dos dados enviados pelo formulário
        $request->validate([
            'username' => 'required|string|max:250|unique:user_', // Nome correto da tabela e coluna
            'email' => 'required|email|max:250',        // Nome correto da tabela e coluna
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

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'profile_picture' => $profilePicturePath,
            'is_public' => $request->has('is_public') ? $request->is_public : true,
            'user_password' => Hash::make($request->password),
        ]);
        $user->save();
        
        
        

        // Autentica o novo usuário
        Auth::login($user);
        $request->session()->regenerate();

        // Redireciona após o registro e login
        return redirect()->route('posts')
            ->with('success', 'You have successfully registered & logged in!');
    }
}
*/