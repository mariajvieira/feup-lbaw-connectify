<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index()
    {
        // Obtém o usuário logado
        $user = auth()->user();

        // Busca os posts visíveis (do próprio usuário, amigos e públicos)
        $posts = $user->Friends_Public_Posts(); 

        // Busca os grupos aos quais o usuário pertence como membro ou proprietário
        $groupsAsMember = $user->groups;  // Grupos onde o usuário é membro
        $ownedGroups = $user->ownedGroups;  // Grupos onde o usuário é proprietário

        // Combina os grupos de membros e administradores
        $groups = $groupsAsMember->merge($ownedGroups);

        // Retorna a view com os dados (posts e grupos relevantes)
        return view('pages.feed', compact('posts', 'groups'));
    }
}
