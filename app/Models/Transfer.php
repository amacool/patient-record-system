<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';

    public $timestamps = false;

    protected $fillable = ['transferred_by', 'transferred_to', 'client_id', 'reason', 'datetime'];

    // Who made the transfer of the client?
    public function transferredby()
    {
        return $this->belongsTo('App\User', 'transferred_by');
    }

    // Who was the client transferred to?
    public function transferredto()
    {
        return $this->belongsTo('App\User', 'transferred_to');
    }

    // Who is the client?
    public function clients()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }
}
