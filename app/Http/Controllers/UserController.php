<?php

namespace App\Http\Controllers;
// namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function createUser()
    {
        return view('partials.createuser');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:250|unique:users',
            'email' => 'required|email|max:250|unique:users',
            'password' => 'required|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'is_public' => 'nullable|boolean',
        ]);
    
        $user = new User();
    
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->is_public = $request->is_public ?? false;
        $user->profile_picture = 'profile_pictures/default.png';
        
    
        $user->save();
    
        return redirect()->route('user', ['id' => $user->id])->with('success', 'Usuário criado com sucesso!');
    }
    
    


    public function getProfile($id)
    {
        // Carregar o usuário, os grupos em que ele é membro e os grupos em que ele é proprietário
        $user = User::with(['groups', 'ownedGroups'])->find($id);
    
        // Verifica se o usuário foi encontrado
        if (!$user) {
            return redirect()->route('home')->with('error', 'Usuário não encontrado');
        }
    
        // Carregar os posts do usuário, ordenados por data
        $posts = $user->posts()->orderBy('post_date', 'desc')->get();
    
        // Retornar a view com o usuário, seus grupos e seus posts
        return view('pages.user', compact('user', 'posts'));
    }


    // Edit user profile
    public function editProfile($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('home')->with('error', 'Usuário não encontrado');
        }

        return view('partials.profileedit', compact('user')); 
    }


    public function getProfilePicture($userId)
    {
        $user = User::findOrFail($userId);
    
        if (!$user->profile_picture) {
            abort(404); 
        }
    
        $filePath = 'images/' . $user->profile_picture;
    
        if (!Storage::exists($filePath)) {
            abort(404);
        }
    
        return response()->file(storage_path('app/' . $filePath));
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
    

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
    
        $request->validate([
            'username' => 'sometimes|string|max:250|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|max:250|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_public' => 'nullable|boolean',
        ]);
    
        // Atualizando os dados do usuário
        if ($request->has('username')) {
            $user->username = $request->username;
        }
    
        if ($request->has('email')) {
            $user->email = $request->email;
        }
    
        if ($request->has('password')) {
            $user->password = Hash::make($request->password); 
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && $user->profile_picture !== 'images/profile_pictures/default.png') {
                $oldFilePath = storage_path('app/public/' . $user->profile_picture);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath); // Remove o arquivo anterior
                }
            }
        
            $profile_picture = $request->file('profile_picture');
            $profile_pictureName = $user->id . '.' . $profile_picture->getClientOriginalExtension();
            $profile_picturePath = $profile_picture->storeAs('images/profile_pictures', $profile_pictureName);
        
            $user->profile_picture = 'profile_pictures/' . $profile_pictureName;
        }



    
        if ($request->has('is_public')) {
            $user->is_public = $request->is_public;
        } else {
            $user->is_public = false;
        }
    
        $user->save();
    
        return redirect()->route('user', ['id' => $user->id])->with('success', 'Perfil atualizado com sucesso!');
    }
    

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
    
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'The old password does not match.'], 400);
        }
    
        // Validação das novas senhas
        $request->validate([
            'new_password' => 'required|min:8|confirmed', 
        ], [
            'new_password.confirmed' => 'The new password and confirmation do not match.',
        ]);
        $user->password = Hash::make($request->new_password);
        $user->save();
    
        return redirect()->route('user', ['id' => $user->id])->with('success', 'Password updated successfully.');
    }
    

    //List users pending firendship request
    public function listPendingRequests()
    {
        $userId = auth()->id();

        
        $pendingRequests = DB::table('friend_request')
            ->join('users', 'friend_request.sender_id', '=', 'users.id')
            ->where('friend_request.receiver_id', $userId)
            ->where('friend_request.request_status', 'pending')
            ->select('friend_request.*', 'users.username as sender_username')
            ->get();

        return view('pages.pending_requests', compact('pendingRequests'));
    }

    public function pendingRequests()
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id')
                    ->where('request_status', 'pending');
    }


    public function showFriendsPage($id)
    {
        $user = User::findOrFail($id);
        $friends = $user->friends; 
        return view('pages.friendsList', compact('user', 'friends'));
    }

    public function getFriends($id)
    {
        $user = User::findOrFail($id);
    
        $friends = DB::table('friendship')
            ->join('users', function($join) use ($user) {
                $join->on('friendship.user_id1', '=', 'users.id')
                     ->orOn('friendship.user_id2', '=', 'users.id');
            })
            ->where(function($query) use ($user) {
                $query->where('friendship.user_id1', '=', $user->id)
                      ->orWhere('friendship.user_id2', '=', $user->id);
            })
            ->where('users.id', '!=', $user->id) 
            ->select('users.id', 'users.username')
            ->get();
    
        return response()->json($friends);
    }
    


    public function promoteToAdmin($userId)
    {
        $user = User::findOrFail($userId);
    
        DB::table('administrator')->insert([
            'user_id' => $userId
        ]);

            return redirect()->route('user', ['id' => $userId])->with('success', 'User promoted to administrator.');    
    }

    
}
