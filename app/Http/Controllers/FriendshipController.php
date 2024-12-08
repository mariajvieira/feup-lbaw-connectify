<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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

    

    public function removeFriend(Request $request, $id)
    {
        try {
            $userId1 = auth()->id();
            $userId2 = $request->input('receiver_id'); 
    
            if (!$userId2) {
                Log::error('Receiver ID não encontrado na requisição.', ['request' => $request->all()]);
                return response()->json(['message' => 'Receiver ID é obrigatório.'], 400);
            }
    
            $deleted = DB::table('friendship')
                ->where(function ($query) use ($userId1, $userId2) {
                    $query->where('user_id1', $userId1)->where('user_id2', $userId2);
                })
                ->orWhere(function ($query) use ($userId1, $userId2) {
                    $query->where('user_id1', $userId2)->where('user_id2', $userId1);
                })
                ->delete();
    
            if ($deleted) {
                Log::info('Friendship succesfully removed.', ['userId1' => $userId1, 'userId2' => $userId2]);
                return response()->json(['message' => 'Friendship removed successfully.']);
            }
    
            Log::warning('Not found', ['userId1' => $userId1, 'userId2' => $userId2]);
            return response()->json(['message' => 'Friendship not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error to remove friendship', ['error' => $e->getMessage(), 'trace' => $e->getTrace()]);
            return response()->json(['message' => 'Erro interno no servidor.'], 500);
        }
    }
    
}
