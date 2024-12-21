<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect() {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle() {

        $google_user = Socialite::driver('google')->stateless()->user();
        $user = User::where('google_id', $google_user->getId())->first();
        
        // If the user does not exist, create one
        if (!$user) {
            // Store the provided name, email, and Google ID in the database
            $new_user = User::create([
                'username' => $this->generateUniqueUsername($google_user->getName()),
                'email' => $google_user->getEmail(),
                'google_id' => $google_user->getId(),
                'is_public' => true,
                'password' => bcrypt(Str::random(16)),
            ]);

            Auth::login($new_user);

        // Otherwise, simply log in with the existing user
        } else {
            Auth::login($user);
        }

        // After login, redirect to homepage
        return redirect()->intended('home');
    }

        protected function generateUniqueUsername($name)
    {
        
        $base_username = Str::slug($name);

        $username = $base_username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base_username . $counter; 
            $counter++;
        }

        return $username;
    }

}
