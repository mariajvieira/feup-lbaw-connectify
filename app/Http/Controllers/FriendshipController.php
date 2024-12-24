<?php

namespace App\Http\Controllers;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;

class FriendshipController extends Controller
{
    public function sendRequest(Request $request)
    {
        $senderId = auth()->id();
        $receiverId = $request->input('receiver_id');

        //Check if profile is public - if it is, add friend automatically
        $receiver = DB::table('users')->where('id', $receiverId)->first();

        //Check if there's a request already
        $existingRequest = DB::table('friend_request')
            ->where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'Request already sent.');
        }

        //Add public friend
        if ($receiver->is_public) {
            DB::table('friendship')->insert([
                'user_id1' => min($senderId, $receiverId),
                'user_id2' => max($senderId, $receiverId),
            ]);
    
            return redirect()->back()->with('success', 'Friend added successfully.');
        }

        else {
        // Create a new friendship request 
            DB::table('friend_request')->insert([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'request_status' => 'pending',
            ]);

            return redirect()->back()->with('success', 'Request sent successfully.');
        }
}

    
    public function acceptRequest($id)
    {
        $friendRequest = DB::table('friend_request')->where('id', $id)->first();

        if (!$friendRequest || $friendRequest->request_status !== 'pending') {
            return response()->json(['message' => 'Invalid request.'], 400);
        }

        // Change request status to accepted
        DB::table('friend_request')->where('id', $id)->update(['request_status' => 'accepted']);

        // Create instance of friendship
        DB::table('friendship')->insert([
            'user_id1' => min($friendRequest->sender_id, $friendRequest->receiver_id),
            'user_id2' => max($friendRequest->sender_id, $friendRequest->receiver_id),
        ]);

        return redirect()->back()->with('success', 'Friend added successfully.');
    }

  
    public function declineRequest($id)
    {
        $friendRequest = DB::table('friend_request')->where('id', $id)->first();

        if (!$friendRequest || $friendRequest->request_status !== 'pending') {
            return response()->json(['message' => 'Invalid Request'], 400);
        }

        // Change request status to declined
        DB::table('friend_request')->where('id', $id)->update(['request_status' => 'denied']);

        return redirect()->back()->with('success', 'Request declined successfully.');
    }

    
    public function remove($friendId)
    {
        $user = auth()->user();
        $profileUserId = request('profile_user_id');
        
        Log::info('Profile User ID: ' . $profileUserId);
        
        // Verifica se o usuário autenticado é o dono do perfil ou se é administrador
        if ($user->id !== $profileUserId && !$user->isAdmin()) {
            return response()->json(['message' => 'Você não tem permissão para remover esta amizade.'], 403);
        }
        
        // Busca o relacionamento de amizade entre os dois usuários
        $friendship = DB::table('friendship')
            ->where(function($query) use ($user, $friendId) {
                $query->where('user_id1', $user->id)
                      ->where('user_id2', $friendId);
            })
            ->orWhere(function($query) use ($user, $friendId) {
                $query->where('user_id1', $friendId)
                      ->where('user_id2', $user->id);
            })
            ->first();
        
        if (!$friendship) {
            return response()->json(['message' => 'Amigo não encontrado.'], 404);
        }
        
        // Remove o relacionamento de amizade
        DB::table('friendship')
            ->where(function($query) use ($user, $friendId) {
                $query->where('user_id1', $user->id)
                      ->where('user_id2', $friendId);
            })
            ->orWhere(function($query) use ($user, $friendId) {
                $query->where('user_id1', $friendId)
                      ->where('user_id2', $user->id);
            })
            ->delete();
    
        return redirect()->route('user', ['id' => $user->id])->with('success', 'Amigo removido com sucesso.');
    }
    
    
    
    

    
}
