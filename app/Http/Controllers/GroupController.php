<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    // Mostrar o formulário para criar um grupo
    public function create()
    {
        return view('partials.creategroup');
    }

    // Armazenar o grupo no banco de dados
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',  // Nome do grupo
            'description' => 'nullable|string',         // Descrição (opcional)
            'is_public' => 'required|boolean',          // Se o grupo é público ou privado
        ]);

        // Criar o grupo no banco de dados
        $group = Group::create([
            'group_name' => $validated['group_name'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),  // Usuário autenticado como proprietário
            'is_public' => $validated['is_public'],
        ]);

        // Adicionar o proprietário à tabela de membros (group_member)
        $group->users()->attach(Auth::id());

        // Redirecionar para a página do grupo
        return redirect()->route('group.show', $group->id);
    }

    // Mostrar um grupo específico
    public function show($groupId)
    {
        // Encontra o grupo com os posts e usuários relacionados
        $group = Group::with(['users', 'posts'])->findOrFail($groupId);
    
        // Pega os membros e os posts associados ao grupo
        $members = $group->users;
        $posts = $group->posts;
    
        // Obter amigos para o owner
        $friends = [];
        if (Auth::id() == $group->owner_id) {
            $friends = Auth::user()->friends()->whereNotIn('id', $members->pluck('id'))->get();
        }
    
        // Retornar a view com os dados
        return view('pages.group', compact('group', 'members', 'posts', 'friends'));
    }
    
    
    public function viewMembers($groupId)
    {
        $group = Group::with('users')->findOrFail($groupId); // Carrega os usuários
        $members = $group->users;
    
        // Obter amigos apenas se o usuário autenticado for o proprietário
        $friends = [];
        if (Auth::id() == $group->owner_id) {
            // Verifica se o usuário é o dono e obtém os amigos que não são membros do grupo
            $friends = Auth::user()->friends()->whereNotIn('id', $members->pluck('id'))->get();
        }
    
        // Passa os dados para a view
        return view('pages.group_members', compact('group', 'members', 'friends'));
    }
    
    

    // Mostrar todos os grupos no feed principal
    public function index()
    {
        $user = auth()->user();

        // Obtém os grupos onde o usuário é membro ou proprietário
        $groupsAsMember = $user->groups; 
        $ownedGroups = $user->ownedGroups; 

        // Combina os grupos
        $allGroups = $groupsAsMember->merge($ownedGroups);

        // Obtém posts visíveis do usuário
        $posts = $user->visiblePosts(); 

        return view('pages.home', compact('posts', 'allGroups'));
    }

    // Permitir que o usuário entre em um grupo público
    public function joinPublicGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Verifica se o grupo é público
        if (!$group->is_public) {
            return response()->json(['message' => 'This is not a public group'], 400);
        }

        // Verifica se o usuário não é membro
        if (!$group->users->contains(Auth::user()->id)) {
            $group->users()->attach(Auth::id());

            return response()->json(['message' => 'Successfully joined the group!'], 200);
        }

        return response()->json(['message' => 'You are already a member of this group.'], 400);
    }

 
    // Deixar um grupo
    public function leaveGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Verifica se o usuário é membro
        if ($group->users->contains(Auth::id())) {
            $group->users()->detach(Auth::id());

            return redirect()->route('group.show', $group->id)->with('message', 'You have left the group successfully.');
        }

        return redirect()->route('group.show', $group->id)->with('error', 'You are not a member of this group.');
    }

    // Remover um membro do grupo
    public function removeMember($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($userId);
    
        // Verifica se o usuário é o owner
        if ($group->owner_id == $user->id) {
            return redirect()->route('group.show', $groupId)->with('error', 'You cannot remove the owner of the group.');
        }
    
        // Remove o usuário do grupo
        $group->users()->detach($userId); 
    
        return redirect()->route('group.show', $groupId)->with('success', 'Member removed successfully.');
    }

    // Adicionar amigo ao grupo
    public function addFriendToGroup(Request $request, $groupId)
{
    $group = Group::findOrFail($groupId);

    // Verifica se o usuário logado é o owner do grupo
    if (Auth::id() !== $group->owner_id) {
        return redirect()->route('group.show', $groupId)->with('error', 'Only the owner can add members to this group.');
    }

    // Verificar se o amigo foi selecionado
    $friendId = $request->input('friend_id');
    $friend = User::findOrFail($friendId);

    // Verifica se o amigo já está no grupo
    if ($friend->isInGroup($groupId)) {
        return redirect()->route('group.show', $groupId)->with('error', 'This friend is already in the group.');
    }

    // Adiciona o amigo ao grupo
    $group->users()->attach($friendId);

    return redirect()->route('group.show', $groupId)->with('success', 'Friend added to the group successfully.');
}

    public function createPost(Group $group)
    {
        if (!$group->users->contains(auth()->user())) {
            return redirect()->route('group.show', $group->id)->with('error', 'You must be a member of the group to post.');
        }

        return view('pages.group_post', compact('group'));
    }

    // Armazenar o post no banco de dados
    public function storePost(Request $request, Group $group)
    {
        // Validar os dados do formulário
        $request->validate([
            'content' => 'required|string|max:1000',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg',
        ]);


        // Criar o post
        $post = new Post();
        $post->content = $request->input('content');
        $post->group_id = $group->id;
        $post->user_id = auth()->id();  


        // Processamento das imagens
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile('image'.$i)) {
                $image = $request->file('image'.$i);

                // Gerar nome do arquivo com base no post ID e número da imagem
                $imageName = $post->id . '.' . $i . '.' . 'jpg';

                $imagePath = $image->storeAs('images/posts', $imageName, 'local'); 
                $post->{'image'.$i} = 'posts/' . $imageName;
            }
        } 

        $post->save();

        return redirect()->route('group.show', $group->id)->with('success', 'Post created successfully.');
    }


    public function showGroupPosts(Group $group)
    {
        // Verificar se o grupo é privado e se o usuário não é membro nem dono
        if ($group->is_public == false && !$group->users->contains(auth()->user()) && $group->owner_id !== auth()->user()->id) {
            // Se não for permitido, redireciona ou retorna uma mensagem de erro
            return redirect()->route('group.show', $group->id)->with('error', 'You do not have permission to view the posts in this group.');
        }

        // Obter os posts do grupo
        $posts = $group->posts()->latest()->get();

        // Retornar a view com os posts
        return view('pages.group_posts', compact('group', 'posts'));
    }


}
