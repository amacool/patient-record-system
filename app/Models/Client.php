<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'user_id', 'firstname', 'lastname', 'born', 'ssn', 'civil_status', 'work_status', 'medication', 'street_address',
        'postal_code', 'city', 'phone', 'closest_relative', 'closest_relative_phone', 'children', 'gp',
        'individual_plan', 'other_info', 'active', 'created_at', 'updated_at', 'oldid'
    ];

    // Find the records for this client
    public function records()
    {
        return $this->hasMany('App\Record');
    }

    // Find the old WP records for a client
    public function wprecords()
    {
        return $this->hasMany('App\Wpposts', 'post_parent', 'oldid');
    }

    // Find the uploaded files for a client
    public function files()
    {
        return $this->hasMany('App\Fileupload');
    }

    // The psychologist having main control over the client. This is referenced by a column 'user_id' in the clients table.
    public function owner() {
        return $this->belongsTo('App\User', 'user_id');
    }

    // All users with access to the client, excluding the owner
    public function user() {
        return $this->belongsToMany('App\User', 'client_user', 'client_id', 'user_id');
    }

    // Accessrights given for this client
    public function accessRights() {
        return $this->hasMany('App\Accessright');
    }

    // Log of read records for this client
    public function readrecords() {
        return $this->hasMany('App\Readrecordlog');
    }

    // Log of changed records for this client
    public function changedrecords() {
        return $this->hasMany('App\Changerecordlog');
    }

    // Log of signed / unsigned records for this client
    public function signlogs() {
        return $this->hasMany('App\Signlog');
    }

    // Log of transfers performed for this client
    public function transfers() {
        return $this->hasMany('App\Transfer');
    }

    // Carbon formatting
    //Må fjernes under import fra wordpress for en del brukere
    public function getDates()
    {
        return array('born');
    }
}
