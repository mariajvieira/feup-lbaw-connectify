<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

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

// Home
Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

// Authentication 
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});



// User
Route::get('/user/{id}', [UserController::class, 'getProfile'])->name('user');

Route::get('/user/{id}/edit', [UserController::class, 'editProfile'])->name('user.edit');
Route::put('/user/{id}', [UserController::class, 'updateProfile'])->name('user.update');

Route::delete('/user/{id}', [UserController::class, 'deleteUser'])->name('user.delete');


// Posts
Route::get('/posts/{id}', [PostController::class, 'show'])->name('post');

Route::get('/post/{id}/edit', [PostController::class, 'edit'])->name('post.edit');
Route::put('/post/{id}', [PostController::class, 'update'])->name('post.update');

Route::delete('/post/{id}', [PostController::class, 'delete'])->name('post.delete');


/*

// Home
Route::redirect('/', '/auth/login');


Route::get('/home', function () {
    return view('home'); 
})->middleware('auth');


Route::controller(LoginController::class)->group(function () {
    Route::get('/auth/login', 'showLoginForm')->name('login');
    Route::post('/auth/login', 'authenticate');
    Route::post('/auth/logout', 'logout')->name('logout'); 
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/auth/register', 'showRegistrationForm')->name('register');
    Route::post('/auth/register', 'register');
});



// Users
Route::controller(UserController::class)->group(function () {
    Route::get('/users/{id}', 'getProfile');
    Route::put('/users/{id}/edit-profile', 'editProfile');
    Route::delete('/users/{id}/delete-profile', 'deleteProfile');
});

// Friendship Request 
Route::controller(FriendshipController::class)->group(function () {
    Route::get('/friendship-requests/send', 'showSendRequestForm')->name('showSendRequestForm');
    Route::post('/friendship-requests/send', 'sendRequest')->name('sendRequest');

    Route::get('/friendship-requests/{requestId}/accept', 'showAcceptRequestForm')->name('showAcceptRequestForm');
    Route::post('/friendship-requests/{requestId}/accept', 'acceptRequest')->name('acceptRequest');

    Route::get('/friendship-requests/{requestId}/reject', 'showRejectRequestForm')->name('showRejectRequestForm');
    Route::post('/friendship-requests/{requestId}/reject', 'rejectRequest')->name('rejectRequest');
});

// About Page
Route::controller(PageController::class)->group(function () {
    Route::get('/about', 'showAboutPage')->name('about');
});

Route::controller(CommentController::class)->group(function () {
    Route::post('/posts/{postId}/comments', 'addComment');
    Route::get('/posts/{postId}/comments', 'getComments');
    Route::put('/comments/{commentId}', 'editComment');
    Route::delete('/comments/{commentId}', 'deleteComment');
});


Route::controller(PostController::class)->group(function () {
    Route::post('/posts/react', 'reactToPost');
    Route::get('/posts/{postId}/reactions', 'getPostReactions');
    Route::post('/comments/react', 'reactToComment');
    Route::get('/comments/{commentId}/reactions', 'getCommentReactions');
});

Route::controller(GroupController::class)->group(function () {
    Route::get('/groups', 'getAllGroups');
    Route::get('/groups/{groupId}', 'getGroup');
    Route::post('/groups/create', 'createGroup');
    Route::post('/groups/{groupId}/edit', 'editGroup');
    Route::delete('/groups/{groupId}', 'deleteGroup');
    Route::post('/groups/{groupId}/request-join', 'requestToJoinGroup');
    Route::post('/groups/{groupId}/leave', 'leaveGroup');
    Route::post('/groups/{groupId}/accept-join-request', 'acceptJoinRequest');
    Route::post('/groups/{groupId}/reject-join-request', 'rejectJoinRequest');
});

Route::controller(NotificationController::class)->group(function () {
    Route::get('/notifications/{userId}/{type}', 'getUserNotifications');
});
*/









/*
// API
Route::controller(CardController::class)->group(function () {
    Route::put('/api/cards', 'create');
    Route::delete('/api/cards/{card_id}', 'delete');
});

Route::controller(ItemController::class)->group(function () {
    Route::put('/api/cards/{card_id}', 'create');
    Route::post('/api/item/{id}', 'update');
    Route::delete('/api/item/{id}', 'delete');
});


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});
*/
