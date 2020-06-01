<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';

    protected $fillable = ['title', 'content', 'category_id', 'created_by', 'created_at', 'updated_at'];

    // Which category does this template have?
    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    // The psychologist who created the template. This is referenced by a column 'created_by' in the templates table.
    public function user() {
        return $this->belongsTo('App\User', 'created_by');
    }
}
