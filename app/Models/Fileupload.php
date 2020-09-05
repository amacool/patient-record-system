<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fileupload extends Model
{
    protected $table = 'files';

    public $timestamps = false;

    protected $fillable = ['user_id', 'client_id', 'file', 'created_at', 'updated_at'];

    // The client a file is connected to
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    // The user uploading the file
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
