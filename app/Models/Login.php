<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = 'logins';


    protected $fillable = ['user_id', 'ip', 'success', 'combocorrect', 'tokencorrect', 'created_at', 'updated_at'];

    // The registered user logging in
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
