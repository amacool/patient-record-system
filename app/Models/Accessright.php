<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accessright extends Model
{
    protected $table = 'accessrights';

    public $timestamps = false;

    protected $fillable = ['given_by', 'revoked_by', 'user_id', 'client_id', 'type', 'role', 'duration', 'reason', 'datetime'];

    // Which client the accessright belongs to
    public function clients()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }

    //Who gave this accessright?
    public function givenby()
    {
        return $this->belongsTo('App\User', 'given_by');
    }

    // Who revoked this accessright?
    public function revokedby()
    {
        return $this->belongsTo('App\User', 'revoked_by');
    }

    // Who was this accessright given to?
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
