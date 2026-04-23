<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class userLogin extends Model
{
    public static function isLogin(): bool
    {
        return !empty(session('user'));
    }

    public static function current(): ?array
    {
        return session('user');
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return session('user.' . $key, $default);
    }
}
