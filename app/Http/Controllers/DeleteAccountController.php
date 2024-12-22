<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Administrator;
use App\Models\Group;

class DeleteAccountController extends Controller
{
    // Método para excluir a conta do usuário autenticado
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
    
        if ($user) {
            // Verificar se o usuário é um administrador
            if ($user->isAdmin()) {
                Administrator::where('user_id', $user->id)->delete();
            }
    
            // ID do novo proprietário ou "usuário genérico" (ID = 0)
            $newOwnerId = 0;
    
            // Remover referências em "friend_request_notification" antes de excluir a "friend_request"
            DB::table('friend_request_notification')
                ->whereIn('friend_request_id', function ($query) use ($user) {
                    $query->select('id')
                          ->from('friend_request')
                          ->where('sender_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })
                ->delete(); // Exclui as referências em "friend_request_notification"
    
            // Remover referências em "friend_request" (enviadas e recebidas)
            DB::table('friend_request')->where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->delete();
    
            // Remover referências em "friendship"
            DB::table('friendship')
                ->where('user_id1', $user->id)
                ->orWhere('user_id2', $user->id)
                ->delete();
    
            // Remover todos os posts salvos pelo usuário
            $user->savedPosts()->detach();
    
            // Remover referências de saved_post relacionadas aos posts do usuário
            $user->posts()->each(function ($post) {
                $post->savedPosts()->detach();
            });
    
            // Remover referências em "comment_notification" antes de excluir as notificações
            DB::table('comment_notification')->whereIn('notification_id', function ($query) use ($user) {
                $query->select('id')
                      ->from('notification')
                      ->where('user_id', $user->id);
            })->delete(); // Exclui as referências em "comment_notification"
    
            // Remover as referências em "reaction_notification" antes de excluir as notificações
            DB::table('reaction_notification')
                ->whereIn('notification_id', function ($query) use ($user) {
                    $query->select('id')
                          ->from('notification')
                          ->where('user_id', $user->id);
                })
                ->delete(); // Exclui as referências em "reaction_notification"
    
            // Remover notificações relacionadas ao usuário
            DB::table('notification')->where('user_id', $user->id)->delete();
    
            // Remover notificações de "join_group_request"
            $groupRequestIds = DB::table('join_group_request')
                ->where('user_id', $user->id)
                ->pluck('id'); // Pegar todos os IDs relacionados ao usuário
    
            DB::table('group_request_notification')
                ->whereIn('group_request_id', $groupRequestIds)
                ->delete(); // Excluir notificações relacionadas
    
            // Remover referências em "join_group_request"
            DB::table('join_group_request')->where('user_id', $user->id)->delete();
    
            // Remover referências em "group_member" (membros dos grupos)
            DB::table('group_member')->where('user_id', $user->id)->delete();
    
            // Remover referências em "group_owner" (proprietário dos grupos)
            DB::table('group_owner')->where('user_id', $user->id)->delete();
    
            // Se o usuário for proprietário de algum grupo, transfira a propriedade para outro usuário
            Group::where('owner_id', $user->id)->update(['owner_id' => $newOwnerId]);
    
            // Transferir a propriedade dos posts para o novo proprietário (ID = 0)
            $user->posts()->update(['user_id' => $newOwnerId]);
    
            // Anonimizar os comentários e reações do usuário (atribuir ao usuário genérico ID = 0)
            $user->comments()->update(['user_id' => $newOwnerId]);
            $user->reactions()->update(['user_id' => $newOwnerId]);
    
            // Excluir o usuário
            $user->delete();
    
            // Retornar resposta de sucesso
            return redirect()->route('home')->with('status', 'Conta excluída com sucesso!');
        }
    
        // Caso não encontre o usuário autenticado
        return redirect()->route('home')->with('error', 'Falha ao excluir a conta!');
    }
}
