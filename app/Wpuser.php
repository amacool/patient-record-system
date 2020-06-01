<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wpuser extends Model
{
    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';

    protected $table = 'wp_users';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = ['newid', 'user_email'];

    // The current user connected to this wp_user
    public function user() {
        return $this->belongsTo('App\User', 'newid');
    }
}
