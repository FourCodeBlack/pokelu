<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kreait\Laravel\Firebase\Facades\Firebase;


class FirebaseHelper extends Model
{
    public static function db($ref = null)
    {
        $db = Firebase::database();
        return $ref ? $db->getReference($ref) : $db->getReference();
    }
    public static function buatParent(string $path, array $data = [])
    {
        self::db($path)->set($data);
    }
    public static function kirim(string $path, array $data = [])
    {
        return self::db($path)
            ->push($data)
            ->getKey();
    }
    public static function perbarui(string $path, array $attributes = [])
    {
        self::db($path)
            ->update($attributes);
    }
    public static function baca(string $path)
    {
        return self::db($path)->getValue();
    }
    public static function hapus(string $path)
    {
        self::db($path)->remove();
    }
    public static function adakah(string $path): bool
    {
        return self::db($path)->getValue() !== null;
    }
    public static function child(string $path, string $child)
    {
        return self::db($path)->getChild($child);
    }
}