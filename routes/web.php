<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
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


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::post('/logout', 'logout')->name('logout'); 
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});


Route::controller(PasswordController::class)->group(function () {
    Route::post('/sendVerificationCode', 'sendVerificationCode'); 
    Route::post('/recoverPassword', 'recoverPassword'); 
});


//Users
Route::controller(UserController::class)->group(function () {
    Route::get('/profile/{id}', 'getProfile');
    Route::put('/profile/{id}', 'editProfile');
    Route::delete('/profile/{id}', 'deleteProfile');
});


// Friendship Request 
Route::controller(FriendshipController::class)->group(function () {
    Route::get('/user/sendRequest', 'showSendRequestForm')->name('showSendRequestForm');
    Route::post('/user/sendRequest', 'sendRequest')->name('sendRequest');

    Route::get('/user/acceptRequest', 'showAcceptRequestForm')->name('showAcceptRequestForm');
    Route::post('/user/acceptRequest', 'acceptRequest')->name('acceptRequest');

    Route::get('/user/rejectRequest', 'showRejectRequestForm')->name('showRejectRequestForm');
    Route::post('/user/rejectRequest', 'rejectRequest')->name('rejectRequest');
});

// About Page
Route::controller(PageController::class)->group(function () {
    Route::get('/about', 'showAboutPage')->name('about');
});



//Comment Routes
Route::controller(CommentController::class)->group(function () {
    Route::post('/posts/{postId}/comments', 'addComment');
    Route::get('/posts/{postId}/commnets',  'getComments');
});















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

