<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'records';

    protected $fillable = ['category_id', 'client_id', 'created_by', 'app_date', 'title', 'content', 'signed_date',
                            'signed_by', 'created_at', 'updated_at', 'oldid'];

    // The client the record is about
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    // The writer of the record
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    // Carbon formatting
    public function getDates()
    {
        return array('app_date', 'signed_date', 'created_at', 'updated_at');
    }

}
