<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = ['name', 'orgnr', 'seats'];

    // The users (psychologists) connected to a company
    public function user() {
        return $this->hasMany('App\User');
    }

    // do something special for users of the company that is being deleted
    public static function boot() {
        parent::boot();
        self::deleting(function($company) {
            $company->user()->each(function($user) {
                // $user->delete();
                return;
            });
        });
    }
}
