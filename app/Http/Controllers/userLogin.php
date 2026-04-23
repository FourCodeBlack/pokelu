<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class userLogin extends Controller
{
    public static function loginSesion()
    {
        $isLoggedIn = User::isLogin();
        $user = User::current();

        return view('card-detail', compact('card', 'isLoggedIn', 'user'));
    }
    public static function avatar(): string
    {
        return session('user.avatar') ?? asset('images/default-avatar.png');
    }
}
