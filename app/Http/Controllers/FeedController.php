<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $posts = $user->Friends_Public_Posts(); 


        return view('pages.feed', compact('posts'));
    }
}
