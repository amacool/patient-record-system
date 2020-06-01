<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wppostmeta extends Model
{
    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';

    protected $table = 'wp_postmeta';

    protected $primaryKey = 'meta_id';

    public $timestamps = false;

    protected $fillable = [];

    // Belongs to a post
    public function wppost() {
        return $this->belongsTo('App\Wpposts', 'post_id', 'ID');
    }
}
