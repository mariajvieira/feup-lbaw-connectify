<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_public' => 'nullable|boolean',
        ]);

        $user = new User();

        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->is_public = $request->is_public ?? false;
        $user->profile_picture = null;

        if ($request->hasFile('profile_picture')) {
            $user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->save();

        return redirect()->route('user', ['id' => $user->id])->with('success', 'Usuário criado com sucesso!');
    }


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
    public function editProfile($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return redirect()->route('home')->with('error', 'Usuário não encontrado');
        }

        return view('partials.profileedit', compact('user')); 
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
            'user_password' => 'nullable|min:8|confirmed',
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

        if ($request->has('user_password')) {
            $user->user_password = Hash::make($request->user_password); 
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && $user->profile_picture !== 'images/profile_pictures/default.png') {
                // Apagando a imagem anterior, caso não seja a imagem padrão
                if (Storage::exists(public_path($user->profile_picture))) {
                    Storage::delete(public_path($user->profile_picture));
                }
            }
    
            // Processando e movendo a nova imagem para o diretório público
            $profile_picture = $request->file('profile_picture');
            $profile_picturePath = 'images/profile_pictures/' . $user->username . '.' . $profile_picture->getClientOriginalExtension();
            $profile_picture->move(public_path('images/profile_pictures'), $user->username . '.' . $profile_picture->getClientOriginalExtension());
            
            // Atualizando o caminho da imagem no banco de dados
            $user->profile_picture = $profile_picturePath;
        }

        if ($request->has('is_public')) {
            $user->is_public = $request->is_public;
        } else {
            $user->is_public = false;
        }

        $user->save();

        return redirect()->route('user', ['id' => $user->id])->with('success', 'Perfil atualizado com sucesso!');
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

    if ($user->id !== auth()->id()) {
        abort(403, 'Acesso não autorizado.');
    }

    return view('pages.friendsList', ['user' => $user]);
}

    public function getFriends($id)
    {
        $user = User::findOrFail($id);

        if ($user->id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $friends = DB::table('friendship')
            ->join('users', function($join) use ($user) {
                $join->on('friendship.user_id1', '=', 'users.id')
                    ->orOn('friendship.user_id2', '=', 'users.id');
            })
            ->where('friendship.user_id1', '=', $user->id)
            ->orWhere('friendship.user_id2', '=', $user->id)
            ->where('users.id', '!=', $user->id) // Exclui o próprio usuário
            ->select('users.id','users.username')
            ->get();


        return response()->json($friends);
    }
}
