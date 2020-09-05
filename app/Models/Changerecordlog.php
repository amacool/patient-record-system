<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Changerecordlog extends Model
{
    protected $table = 'changerecordlog';

    public $timestamps = false;

    protected $fillable = ['created_by', 'changed_by', 'client_id', 'record_id', 'formertitle', 'newtitle', 'formercontent',
        'newcontent', 'formerapp_date', 'newapp_date', 'timestamp'];

    // Who changed this record?
    public function user()
    {
        return $this->belongsTo('App\User', 'changed_by');
    }

    // Which client does this record belong to?
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    // Carbon formatting
    public function getDates()
    {
        return array('formerapp_date', 'newapp_date', 'timestamp');
    }
}
