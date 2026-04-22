<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Exists;
use Kreait\Laravel\Firebase\Facades;
use Kreait\Laravel\Firebase\Facades\Firebase;

use function Symfony\Component\String\s;

class pokecardFirebase extends Model
{
    protected static string $ref = "pokemon_card";
    public static function db(){
        return Firebase::database()->getReference(self::$ref);
    }
    public static function getAll(){
        $snapshot = self::db()->getSnapshot();
        return $snapshot->exists()?array_values($snapshot->getValue()):[];
    }
    public static function exists():bool{
        return self::db()->getSnapshot()->exists();
    }
    public static function set(array $data){
        self::db()->set($data);
    }
}
