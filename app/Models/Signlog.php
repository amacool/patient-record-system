<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Signlog extends Model
{
    protected $table = 'signlog';

    public $timestamps = false;

    protected $fillable = ['client_id', 'record_id', 'signed_by', 'unsigned_by', 'timestamp'];

    // Which user signed the record?
    public function signedby()
    {
        return $this->belongsTo('App\User', 'signed_by');
    }

    // Which user unsigned the record?
    public function unsignedby()
    {
        return $this->belongsTo('App\User', 'unsigned_by');
    }
}
