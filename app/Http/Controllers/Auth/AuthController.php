<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    private $redirectTo = '/loggedin';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['getLogout', 'getRegister', 'postRegister']]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'required|Numeric',
            'password' => 'required|confirmed|min:8|regex:/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/',

        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = \Auth::user();

        // If authenticated user is admin, create the new user. Two factor authentication (tfa)
        // is on (1) by default

        if ($user->role == 2) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),

                //CUSTOM
                'role' => $data['role_id'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'],
                'company_id' => $data['company_id'],
                'created_by' => $data['created_by'],
                'tfa' => '0',
                'active' => true
            ]);
        }

        // If authenticated user is company admin, create the new user. Two factor authentication (tfa)
        // is on (1) by default

        if ($user->role == 1) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),

                //CUSTOM
                'role' => 0,
                'phone' => $data['phone'],
                'country_code' => $data['country_code'],
                'company_id' => $user->company->id,
                'created_by' => $data['created_by'],
                'tfa' => '0',
                'active' => true
            ]);
        }
    }

    /**
     * *  OVERRIDING THE METHOD IN RegistersUsers.php
     * Show the application registration form.
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        $user = \Auth::user();

        // If user has role of admin, allow showing admin-page for registration of new user
        if ($user->role == 2) {
            $companies = \App\Company::lists('name', 'id');
            return view('auth.aregister', compact('companies'));
        }

        // If user has role of "company admin", allow showing of registration of new users for this specific company
        if ($user->role == 1) {
            $company = $user->company;
            return view('auth.caregister', compact('company'));
        }

        // If neither system admin or company admin, redirect to home page
        return redirect()->route('home')->with('message', 'You are trying to perform an illegal action.');

    }

    /**
     * * OVERRIDING THE METHOD IN RegistersUsers.php
     * Handle a registration request for the application.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // NEW postRegister function, with AUTHY
    public function postRegister(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails())
        {
            $this->throwValidationException(
                $request, $validator
            );
        }

        // Create the user if it passes validation
        // The create function will check if user is allowed to create a new user (admins and companyadmins)
        $user = $this->create($request->all());

        // Register the new user with Authy. This will include an sms to the user that he has been registered,
        // and also a link to download the app
        //$user->register_authy();

        return redirect($this->redirectPath());
    }

    /**
     * * OVERRIDING THE METHOD IN AuthenticatesUsers.php
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // postLogin with Authy functionality
    public function postLogin(Request $request) {
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('email','password');

        if(Auth::validate($credentials)) {
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();

            // If correct username + password, and the user has 2fa ON
            if ($user->tfa == 1) {
                if ($throttles) {
                    $this->clearLoginAttempts($request);
                }

                // Log that the user has the u+p combination correct
                $loginlog = new \App\Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = 'Correct';
                $loginlog->tokencorrect = 'Awaiting';
                $loginlog->save();

                // Send token either trough App (default) or sms if former not supported
                $user->sendToken();
                // Set session variables
                Session::set('password_validated', true);
                Session::set('id', $user->id);
                // Redirect to page for twofactor authentication
                return redirect()->route('twofactor');
            }

            // If correct username + password, and the user has 2fa OFF
            if ($user->tfa == 0) {
                if ($throttles) {
                    $this->clearLoginAttempts($request);
                }

                // Log that the user has the u+p combination correct and is logged in
                $loginlog = new \App\Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = 'Correct';
                $loginlog->success = 'Success';
                $loginlog->save();

                // Log the user in
                Auth::login($user);
                return redirect($this->redirectPath());
            }
        }

        // If username password combination is wrong, log the attempt
        // Check if we have a valid username
        $user = User::where('email', '=', $request->input('email'))->first();

        $loginlog = new \App\Login;
        // If valid userid, log that, else leave it empty
        if ($user !== null) {$loginlog->user_id = $user->id;}
        $loginlog->ip = $request->getClientIp();
        $loginlog->combocorrect = 'Error';
        $loginlog->save();

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return redirect($this->loginPath())
            ->withInput($request->only($this->loginUsername(), 'remember'))
            ->withErrors([
                $this->loginUsername() => $this->getFailedLoginMessage(),
            ]);

            //return redirect()->route('loginpage')->with('message', 'Your combination is wrong. Try again.');
    }

    // USE THIS FOR LOGIN IF NOT LOGIN THROTTLING IS WANTED
    // postLogin with Authy functionality - But without login throttling
    public function postLoginOLD(Request $request) {
        $credentials = $request->only('email','password');


        if(Auth::validate($credentials)) {
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();

            // If correct username + password, and the user has 2fa ON
            if ($user->tfa == 1) {

                // Log that the user has the u+p combination correct
                $loginlog = new \App\Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = 'Correct';
                $loginlog->tokencorrect = 'Awaiting';
                $loginlog->save();

                // Send token either trough App (default) or sms if former not supported
                $user->sendToken();
                // Set session variables
                Session::set('password_validated', true);
                Session::set('id', $user->id);
                // Redirect to page for twofactor authentication
                return redirect()->route('twofactor');
            }

            // If correct username + password, and the user has 2fa OFF
            if ($user->tfa == 0) {
                // Log that the user has the u+p combination correct and is logged in
                $loginlog = new \App\Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = 'Correct';
                $loginlog->success = 'Success';
                $loginlog->save();

                // Log the user in
                Auth::login($user);
                return redirect($this->redirectPath());
            }
        }

        // If username password combination is wrong, log the attempt
        // Check if we have a valid username
        $user = User::where('email', '=', $request->input('email'))->first();

        $loginlog = new \App\Login;
        // If valid userid, log that, else leave it empty
        if ($user !== null) {$loginlog->user_id = $user->id;}
        $loginlog->ip = $request->getClientIp();
        $loginlog->combocorrect = 'Error';
        $loginlog->save();

        return redirect()->route('loginpage')->with('message', 'Your combination is wrong. Try again.');
    }

    // AUTHY redirect to two-factor page
    public function getTwofactor() {
        $user = User::find(Session::get('id'));

        if ($user == null) {
            return redirect()->route('home');
        }

        return view('auth/twofactor');
    }

    // AUTHY verify when two factor form is posted
    public function postTwofactor(Request $request) {
        if(!Session::get('password_validated') || !Session::get('id')) {
            return redirect()->route('loginpage');
        }

        if(isset($_POST['token'])) {
            $user = User::find(Session::get('id'));
            if($user->verifyToken($request->input('token'))) {
                Auth::login($user);

                // Log that the user was successfully logged in
                $loginlog = new \App\Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->tokencorrect = 'Correct';
                $loginlog->success = 'Success';
                $loginlog->save();

                return redirect($this->redirectPath());
            } else {
                // Log that the user entered wrong token
                $loginlog = new \App\Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->tokencorrect = 'Error';
                $loginlog->save();

                Session::flush();
                return redirect()->route('logout');
            }
        }
    }

    /**OVERRIDE
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        // Custom function to log that the user logs off
        if (Auth::user()) {
            $user = Auth::user();
            // Log that the user manually logged off
            $logoutlog = new \App\Logout;
            $logoutlog->user_id = $user->id;
            $logoutlog->manual = 'yes';
            $logoutlog->save();
        }

        Auth::logout();
        Session::flush();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

}
