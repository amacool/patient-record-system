<?php

namespace App;

use Authy\AuthyApi as AuthyApi;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *FOR AUTHY: 'phone','country_code', 'tfa'
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'country_code', 'company_id', 'created_by', 'tfa', 'oldid', 'active'];

    /**
     * The attributes excluded from the model's JSON form.
     *FOR AUTHY: 'authy_id'
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'authy_id'];

    // The user's clients
    public function clients() {
        return $this->hasMany('App\Client');
    }

    // The user's templates
    public function templates() {
        return $this->hasMany('App\Template', 'created_by');
    }

    // The user's records
    public function records() {
        return $this->hasMany('App\Record', 'created_by');
    }

    // The user's company
    public function company() {
        return $this->belongsTo('App\Company');
    }

    // The clients the user has been given access to by other psychologists, but where the client has not been transferred
    public function coopclients() {
        return $this->belongsToMany('App\Client', 'client_user', 'user_id', 'client_id');
    }

    // Files uploaded by the user
    public function files() {
        return $this->hasMany('App\Fileupload');
    }

    // Logins made by the user
    public function logins() {
        return $this->hasMany('App\Login');
    }

    // Records that have been changed by the user
    public function changerecord() {
        return $this->hasMany('App\Changerecordlog', 'changed_by');
    }

    // Records that have been read by the user
    public function readrecord() {
        return $this->hasMany('App\Readrecordlog', 'read_by');
    }

    // Instances where the user gave access to clients to other psychologists
    public function gaveaccess() {
        return $this->hasMany('App\Accessright', 'given_by');
    }

    // Instances where the user revoked access to clients from other psychologists
    public function revokedaccess() {
        return $this->hasMany('App\Accessright', 'revoked_by');
    }

    // Instances where the psychologist was given access to clients by other psychologists
    public function givenaccess() {
        return $this->hasMany('App\Accessright', 'user_id');
    }

    // Instances where the user transferred a client to another psychologist
    public function transferredclient() {
        return $this->hasMany('App\Transfer', 'transferred_by');
    }

    // Instances where the psychologist received a client (transfer) from another psychologist
    public function receivedclient() {
        return $this->hasMany('App\Transfer', 'transferred_to');
    }

    // The old wp_user connected to this user
    public function olduser() {
        return $this->belongsTo('App\Wpuser', 'oldid');
    }

    // The old WP posts connected to this user
    public function wpposts() {
        return $this->hasMany('App\Wpposts', 'post_author', 'oldid');
    }

    // AUTHY FUNKSJONENE LIGGER I USERCONTROLLER. TROR EGENTLIG IKKE JEG TRENGER DE HER
    // AUTHY REGISTER USER
    public function register_authy() {
        $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
        $user = $authy_api->registerUser($this->email, $this->phone, $this->country_code);

        if($user->ok()) {
            $this->authy_id = $user->id();
            $this->save();
        } else {
            // something went wrong
        }
    }

    // AUTHY SEND SMS
    public function sendToken() {
        $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
        $sms = $authy_api->requestSms($this->authy_id);

        return $sms->ok();
    }

    // AUTHY verify token
    public function verifyToken($token) {
        $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
        $verification = $authy_api->verifyToken($this->authy_id, $token);

        if($verification->ok()) {
            return true;
        } else {
            return false;
        }
    }
}
