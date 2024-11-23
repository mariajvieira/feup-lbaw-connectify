<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Get user profile by Id
    public function getProfile($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->route('home')->with('error', 'Usuário não encontrado');
        }
    
        // Carregar apenas os posts do usuário, ordenados por data
        $posts = $user->posts()->orderBy('post_date', 'desc')->get();
    
        return view('pages.user', compact('user', 'posts'));
    }

    // Edit user profile
    public function editProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'username' => 'sometimes|string|max:250|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:250|unique:users,email,' . $user->id,
            'user_password' => 'nullable|min:8|confirmed',
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_public' => 'nullable|boolean',
        ]);

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('user_password')) {
            $user->user_password = Hash::make($request->user_password); 
        }

        if ($request->hasFile('profilePicture')) {
            if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
                Storage::delete('public/' . $user->profile_picture);
            }
            $user->profile_picture = $request->file('profilePicture')->store('profile_pictures', 'public');
        }

        if ($request->has('is_public')) {
            $user->is_public = $request->is_public;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }

    // Delete user
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
            Storage::delete('public/' . $user->profile_picture);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
