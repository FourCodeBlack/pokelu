<?php

namespace App\Http\Controllers;

use App\Models\FirebaseHelper;
use Illuminate\Http\Request;

class userProfile extends Controller
{
    protected static String $ref = 'users/';
    public static function getUser(String $uid){
        $user = FirebaseHelper::baca(self::$ref.$uid);
        return $user;
    }
}  
