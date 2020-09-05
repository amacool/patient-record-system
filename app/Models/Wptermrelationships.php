<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wptermrelationships extends Model
{
    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';

    protected $table = 'wp_term_relationships';

    protected $primaryKey = 'object_id';

    public $timestamps = false;

    protected $fillable = [];

    // Belongs to a post
    public function wppost() {
        return $this->belongsTo('App\Wpposts', 'object_id', 'ID');
    }
}
