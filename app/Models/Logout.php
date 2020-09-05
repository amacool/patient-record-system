<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Logout extends Model
{
    protected $table = 'logouts';


    protected $fillable = ['user_id', 'ip', 'manual', 'auto', 'created_at', 'updated_at'];

}
