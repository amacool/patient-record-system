<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Login;
use App\Logout;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('getLogout');
    }

    public function postLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ], [
            'email.required' => 'Du må fylle inn brukernavn',
            'password.required' => 'Du må fylle inn passord'
        ]);

        if ($validator->fails()) {
          return redirect('/auth/login')
            ->withInput($request->only('email', 'remember'))
            ->withErrors($validator);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::validate($credentials)) {
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();

            // If correct username + password, and the user has 2fa ON
            if ($user->tfa == 1) {
                // Send token either trough App (default) or sms if former not supporte

                $loginlog = new Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->success = '';
                $loginlog->combocorrect = 'Correct';
                $loginlog->tokencorrect = 'Awaiting';
                $loginlog->save();

                if (!$user->sendToken()) {
                    // $loginlog->success = '';
                    // $loginlog->combocorrect = '';
                    // $loginlog->tokencorrect = 'Error';
                }

                // Set session variables
                Session(['password_validated' => true, 'id' => $user->id]);

                // Redirect to page for twofactor authentication
                return redirect()->route('two-factor');
            } else if ($user->tfa == 0) {
                // If correct username + password, and the user has 2fa OFF
                // Log that the user has the u+p combination correct and is logged in
                $loginlog = new Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = 'Correct';
                $loginlog->success = 'Success';
                $loginlog->tokencorrect = '';
                $loginlog->save();

                // Log the user in
                Auth::login($user);
                return redirect('/clients/active');
            }
        }

        // If username password combination is wrong, log the attempt
        // Check if we have a valid username
        $user = User::where('email', '=', $request->input('email'))->first();
        $loginlog = new Login;

        // If valid userid, log that, else leave it empty
        if ($user !== null) {
            $loginlog->user_id = $user->id;
        }

        $loginlog->ip = $request->getClientIp();
        $loginlog->combocorrect = 'Error';
        $loginlog->success = '';
        $loginlog->tokencorrect = '';
        $loginlog->save();

        return redirect('/auth/login')
            ->withInput($request->only('email', 'remember'))
            ->withErrors(trans('auth.failed'));
    }

    public function getLogin(Request $request) {
        return view('auth.login');
    }

    public function getLogout(Request $request)
    {
        $user = Auth::user();
        // Custom function to log that the user logs off
        if ($user) {
            // Log that the user manually logged off
            $logoutlog = new Logout;
            $logoutlog->user_id = $user->id;
            $logoutlog->manual = 'yes';
            $logoutlog->auto = '';
            $logoutlog->ip = $request->getClientIp();
            $logoutlog->save();
        }

        Auth::logout();
        Session::flush();

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }

    // AUTHY redirect to two-factor page
    public function getTwoFactor() {
        $user = User::find(Session::get('id'));

        if ($user == null) {
            return redirect('auth/login');
        }

        return view('auth/twofactor');
    }

    // AUTHY verify when two factor form is posted
    public function postTwoFactor(Request $request) {
        if (!Session::get('password_validated') || !Session::get('id')) {
            return redirect()->route('login-page');
        }

        if (isset($_POST['token'])) {
            $user = User::find(Session::get('id'));
            if ($user->verifyToken($request->input('token'))) {
                Auth::login($user);

                // Log that the user was successfully logged in
                $loginlog = new Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = '';
                $loginlog->tokencorrect = 'Correct';
                $loginlog->success = 'Success';
                $loginlog->save();

                //return redirect($this->redirectPath());
                return redirect('/clients/active');
            } else {
                // Log that the user entered wrong token
                $loginlog = new Login;
                $loginlog->user_id = $user->id;
                $loginlog->ip = $request->getClientIp();
                $loginlog->combocorrect = '';
                $loginlog->tokencorrect = 'Error';
                $loginlog->success = '';
                $loginlog->save();

                Session::flush();
                return redirect()->route('logout');
            }
        }
    }
}
