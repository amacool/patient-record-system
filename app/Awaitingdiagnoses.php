<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Awaitingdiagnoses extends Model
{
    protected $table = 'awaitingdiagnoses';

    public $timestamps = false;

    protected $fillable = ['client_id', 'oldclient_id', 'user_id', 'olduser_id', 'title', 'content', 'awaiting'];

}
