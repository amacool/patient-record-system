<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Readrecordlog extends Model
{
    protected $table = 'readrecordlog';

    public $timestamps = false;

    protected $fillable = ['read_by', 'client_id', 'record_id', 'timestamp'];

    // The logged in user who read the record
    public function user()
    {
        return $this->belongsTo('App\User', 'read_by');
    }

    // The client the record is about
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

}
