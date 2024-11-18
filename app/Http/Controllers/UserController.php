<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    
    //Get user profile by Id
    public function getProfile($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'profilePicture' => $user->profilePicture,
            'isPublic' => $user->isPublic
        ]);
    }

  
    //Edit user profile
    public function editProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'username' => 'sometimes|string|max:250|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:250|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'isPublic' => 'nullable|boolean',
        ]);

    
        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profilePicture')) {
            if ($user->profilePicture && Storage::exists('public/' . $user->profilePicture)) {
                Storage::delete('public/' . $user->profilePicture);
            }
            $user->profilePicture = $request->file('profilePicture')->store('profile_pictures', 'public');
        }

        if ($request->has('isPublic')) {
            $user->isPublic = $request->isPublic;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }

    //Delete user
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->profilePicture && Storage::exists('public/' . $user->profilePicture)) {
            Storage::delete('public/' . $user->profilePicture);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
