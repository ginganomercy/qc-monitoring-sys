<?php

use Illuminate\Support\Facades\Route;

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

use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::get('/quick-login/{role}', function ($role) {
    if ($role === 'admin') {
        $user = User::where('role', User::ROLE_ADMIN)->first();
        if ($user) Auth::login($user);
        return redirect('/admin');
    }

    if ($role === 'leader') {
        $user = User::where('role', User::ROLE_LEADER)->first();
        if ($user) Auth::login($user);
        return redirect('/leader');
    }

    return redirect('/');
})->name('quick.login');
