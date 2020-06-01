<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Awaitingupload extends Model
{
    protected $table = 'awaitingupload';

    public $timestamps = false;

    protected $fillable = ['client_id', 'oldclient_id', 'user_id', 'olduser_id', 'filename', 'awaiting'];

}
