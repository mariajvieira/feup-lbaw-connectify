<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Administrator;
use App\Models\Group;
use App\Models\User; // Importar o modelo de User para verificar as contas

class DeleteAccountController extends Controller
{
    // Método para excluir a conta do usuário autenticado ou de outro usuário (se for administrador)
    public function deleteAccount(Request $request, $userId = null)
    {
        $user = Auth::user();
    
        if ($user) {
            // Se o ID do usuário não for fornecido, o usuário está tentando excluir a própria conta
            $targetUserId = $userId ?? $user->id;

            // Verificar se o usuário é um administrador e está tentando excluir a conta de outro usuário
            if ($user->isAdmin() && $targetUserId != $user->id) {
                $targetUser = User::find($targetUserId);
                
                if ($targetUser) {
                    // Lógica para apagar a conta de outro usuário
                    $newOwnerId = 0; // Usar 0 ou outro ID genérico para a transferência de propriedade

                    // Remover dados do usuário a ser excluído
                    DB::table('friend_request_notification')->whereIn('friend_request_id', function ($query) use ($targetUser) {
                        $query->select('id')
                              ->from('friend_request')
                              ->where('sender_id', $targetUser->id)
                              ->orWhere('receiver_id', $targetUser->id);
                    })->delete();

                    DB::table('friend_request')->where('sender_id', $targetUser->id)->orWhere('receiver_id', $targetUser->id)->delete();
                    DB::table('friendship')->where('user_id1', $targetUser->id)->orWhere('user_id2', $targetUser->id)->delete();
                    $targetUser->savedPosts()->detach();
                    $targetUser->posts()->each(function ($post) {
                        $post->savedPosts()->detach();
                    });

                    // Remover as referências em "comment_notification" e "reaction_notification"
                    DB::table('comment_notification')->whereIn('notification_id', function ($query) use ($targetUser) {
                        $query->select('id')->from('notification')->where('user_id', $targetUser->id);
                    })->delete();

                    DB::table('reaction_notification')->whereIn('notification_id', function ($query) use ($targetUser) {
                        $query->select('id')->from('notification')->where('user_id', $targetUser->id);
                    })->delete();

                    DB::table('notification')->where('user_id', $targetUser->id)->delete();

                    // Remover referências de grupo e transferir a propriedade
                    DB::table('join_group_request')->where('user_id', $targetUser->id)->delete();
                    DB::table('group_member')->where('user_id', $targetUser->id)->delete();
                    DB::table('group_owner')->where('user_id', $targetUser->id)->delete();
                    Group::where('owner_id', $targetUser->id)->update(['owner_id' => $newOwnerId]);

                    // Transferir posts e anonimizar comentários e reações
                    $targetUser->posts()->update(['user_id' => $newOwnerId]);
                    $targetUser->comments()->update(['user_id' => $newOwnerId]);
                    $targetUser->reactions()->update(['user_id' => $newOwnerId]);

                    // Excluir a conta do usuário
                    $targetUser->delete();

                    return redirect()->route('home')->with('status', 'Conta do usuário excluída com sucesso!');
                }
                return redirect()->route('home')->with('error', 'Usuário não encontrado.');
            }

            // Caso não seja administrador, ou se for o próprio usuário tentando excluir a conta
            if ($targetUserId == $user->id) {
                // Lógica de exclusão da conta do próprio usuário
                $newOwnerId = 0; // Transferir a propriedade para um usuário genérico

                // Remover dados do usuário
                DB::table('friend_request_notification')->whereIn('friend_request_id', function ($query) use ($user) {
                    $query->select('id')
                          ->from('friend_request')
                          ->where('sender_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })->delete();

                DB::table('friend_request')->where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->delete();
                DB::table('friendship')->where('user_id1', $user->id)->orWhere('user_id2', $user->id)->delete();
                $user->savedPosts()->detach();
                $user->posts()->each(function ($post) {
                    $post->savedPosts()->detach();
                });

                // Remover as referências em "comment_notification" e "reaction_notification"
                DB::table('comment_notification')->whereIn('notification_id', function ($query) use ($user) {
                    $query->select('id')->from('notification')->where('user_id', $user->id);
                })->delete();

                DB::table('reaction_notification')->whereIn('notification_id', function ($query) use ($user) {
                    $query->select('id')->from('notification')->where('user_id', $user->id);
                })->delete();

                DB::table('notification')->where('user_id', $user->id)->delete();

                // Remover referências de grupo e transferir a propriedade
                DB::table('join_group_request')->where('user_id', $user->id)->delete();
                DB::table('group_member')->where('user_id', $user->id)->delete();
                DB::table('group_owner')->where('user_id', $user->id)->delete();
                Group::where('owner_id', $user->id)->update(['owner_id' => $newOwnerId]);

                // Transferir posts e anonimizar comentários e reações
                $user->posts()->update(['user_id' => $newOwnerId]);
                $user->comments()->update(['user_id' => $newOwnerId]);
                $user->reactions()->update(['user_id' => $newOwnerId]);

                // Excluir a conta do usuário
                $user->delete();

                return redirect()->route('home')->with('status', 'Sua conta foi excluída com sucesso!');
            }

            return redirect()->route('home')->with('error', 'Você não tem permissão para excluir esta conta.');
        }

        // Caso não encontre o usuário autenticado
        return redirect()->route('home')->with('error', 'Falha ao excluir a conta!');
    }
}

