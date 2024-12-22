<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SavedPostController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\DeleteAccountController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Home
Route::get('/', [PublicHomeController::class, 'index'])->name('welcome');


// Home (Protected)
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// Feed (Protected)
Route::middleware('auth')->group(function () {
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
});


// About us page
Route::get('/about', function () {
    return view('pages/about'); 
})->name('about');

//Show friends 
Route::middleware('auth')->group(function () {
    Route::get('/user/{id}/friends/json', [UserController::class, 'getFriends'])->name('user.getfriends');
    Route::post('/friendship/remove/{id}', [FriendshipController::class, 'removeFriend'])->name('friendship.remove');
    Route::get('/user/{id}/friendspage', [UserController::class, 'showFriendsPage'])->name('user.friendsPage');
});


// Authentication 
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::post('/logout', 'logout')->name('logout'); 
    Route::post('sendEmail', 'sendEmail')->name('sendEmail');
    Route::get('verifyCodePage', 'verifyCodePage')->name('verifyCodePage');
    Route::post('verifyCode', 'verifyCode')->name('verifyCode');
    Route::get('forgotPassword', 'forgotPassword')->name('forgotPassword');;
    Route::get('resetPasswordPage', 'resetPasswordPage')->name('resetPasswordPage');
    Route::post('resetPassword', 'resetPassword')->name('resetPassword');
    
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// User
Route::get('/user/create', [UserController::class, 'createUser'])->name('user.create');
Route::post('/user/create', [UserController::class, 'storeUser'])->name('user.store');
Route::get('/user/{id}', [UserController::class, 'getProfile'])->name('user');
Route::get('/user/{id}/edit', [UserController::class, 'editProfile'])->name('user.edit');
Route::put('/user/{id}/update-password', [UserController::class, 'updatePassword'])->name('user.updatePassword');
Route::get('user/{id}/pending-requests', [UserController::class, 'listPendingRequests'])->name('user.listRequests');
Route::get('/user/{id}/friends', [UserController::class, 'getFriends'])->name('user.friends');
Route::put('/user/{id}', [UserController::class, 'updateProfile'])->name('user.update');
Route::delete('/user/{id}', [UserController::class, 'deleteUser'])->name('user.delete');
Route::get('/profile-picture/{id}', [UserController::class, 'getProfilePicture'])->name('profile.picture');
Route::get('/post/{post}/reactions', [PostController::class, 'showReactionsPage'])->name('post.reactions');


// Posts
Route::get('/post/create', [PostController::class, 'create'])->name('post.create');
Route::get('/post/{id}', [PostController::class, 'show'])->name('post');
Route::get('/post/{id}/edit', [PostController::class, 'edit'])->name('post.edit');
Route::post('/post/store', [PostController::class, 'store'])->name('post.store');
Route::get('post/{postId}/image/{imageNumber}', [PostController::class, 'getPostImage'])->name('post.image');


// Save and unsave post
Route::post('/save-post', [SavedPostController::class, 'savePost'])->name('save.post');
Route::post('/remove-save-post', [SavedPostController::class, 'removeSavePost'])->name('remove.save.post');

// Friendship Requests
Route::post('/friend-request/send', [FriendshipController::class, 'sendRequest'])->name('friend-request.send');
Route::post('/friend-request/{id}/accept', [FriendshipController::class, 'acceptRequest'])->name('friend-request.accept');
Route::post('/friend-request/{id}/decline', [FriendshipController::class, 'declineRequest'])->name('friend-request.decline');
Route::post('/friend-request/{id}/remove', [FriendshipController::class, 'removeFriend'])->name('friend-request.remove');

// Search
Route::get('api/search', [UserSearchController::class, 'search'])->name('search');

// Reactions
Route::post('/post/{id}/reaction', [ReactionController::class, 'store'])->name('reaction.store');
Route::delete('/reaction/{id}', [ReactionController::class, 'destroy'])->name('reaction.destroy');


// Comments
Route::post('/post/{id}/comment', [CommentController::class, 'store'])->name('comment.store');
Route::delete('/comment/{id}', [CommentController::class, 'destroy'])->name('comment.destroy');
Route::put('/comments/{id}/edit', [CommentController::class, 'update'])->name('comment.update');



// API Routes
Route::prefix('api')->group(function () {
    // Posts API Routes
    Route::put('/post/{id}', [PostController::class, 'update'])->name('post.update');
    Route::delete('/post/{id}', [PostController::class, 'delete'])->name('post.delete');
    
    // User API Routes
    //Route::put('/user/{id}', [UserController::class, 'updateProfile']); 
    //Route::delete('/user/{id}', [UserController::class, 'deleteUser']); 
});


// Mostrar formulário de criação do grupo
Route::get('/group/create', [GroupController::class, 'create'])->name('group.create');

// Armazenar novo grupo
//Route::post('/group/store', [GroupController::class, 'store'])->name('group.store');
Route::get('/group/{id}', [GroupController::class, 'show'])->name('group.show');
Route::post('/group', [GroupController::class, 'store'])->name('group.store');
Route::get('/saved-posts', [PostController::class, 'showSavedPosts'])->name('saved.posts');

// Go to the group page
Route::get('/feed', [GroupController::class, 'index'])->name('feed'); // Rota para o feed principal
Route::get('/group/{id}', [GroupController::class, 'show'])->name('group.show'); // Rota para exibir os detalhes de um grupo específico
Route::get('/feed', [FeedController::class, 'index'])->name('feed');


//Join public group
Route::post('/groups/{groupId}/join', [GroupController::class, 'joinPublicGroup'])->name('groups.join');

//Tagged posts
Route::get('/tagged-posts', [PostController::class, 'showTaggedPosts'])->name('tagged.posts')->middleware('auth');

Route::get('/contact', [ContactController::class, 'showContactForm'])->name('contact');
Route::post('/contact', [ContactController::class, 'sendContactEmail'])->name('contact.send');


// Google Auth
Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirect')->name('google-auth');
    Route::get('auth/google/call-back', 'callbackGoogle')->name('google-call-back');
});

// Delete user
Route::delete('/delete-account/{userId?}', [DeleteAccountController::class, 'deleteAccount'])->name('delete.account');
Route::get('/group/{groupId}/members', [GroupController::class, 'viewMembers'])->name('group.members');
Route::get('/groups/{groupId}/leave', [GroupController::class, 'leaveGroup'])->name('group.leave');

//Remove group member as owner
Route::delete('/group/{group}/remove/{user}', [GroupController::class, 'removeMember'])->name('group.removeMember');
Route::get('/group/{group}/members', [GroupController::class, 'showMembers'])->name('group.members');
