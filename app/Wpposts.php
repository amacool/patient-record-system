<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wpposts extends Model
{
    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';

    protected $table = 'wp_posts';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [];

    // The current user connected to this wp_user
    public function user() {
        return $this->belongsTo('App\User', 'post_author', 'oldid');
    }

    // Has many meta
    public function wppostmeta() {
        return $this->hasMany('App\Wppostmeta', 'post_id', 'ID');
    }

    // Belongs to termrelationship
    public function wpterm() {
        return $this->belongsTo('App\Wptermrelationships', 'ID', 'object_id');
    }

    public function client() {
        return $this->belongsTo('App\Client', 'post_parent', 'oldid');
    }
}
