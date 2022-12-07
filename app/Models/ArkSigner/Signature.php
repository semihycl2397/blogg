<?php

namespace App\Models\ArkSigner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;
    public function getApplication(){
        return $this -> hasMany('App\Models\Application','application_id','id');
    }

}
