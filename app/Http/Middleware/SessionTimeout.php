<?php namespace App\Http\Middleware;

use Illuminate\Support\Facades\Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\Store;

class SessionTimeout {
    protected $session;
    protected $timeout=3600; // In seconds

    // CUSTOM MIDDLEWARE TO TIMEOUT IDLE USERS AND LOG THE EVENT

    public function __construct(Store $session){
        $this->session=$session;
    }
    /**
     * CUSTOM MIDDLEWARE TO TIME OUT USERS AFTER CERTAIN TIME OF INACTIVITY
     * WILL ALSO LOG THE EVENT
     *
     * Use it by referencing middleware "timeout"
     *
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$this->session->has('lastActivityTime'))
            $this->session->put('lastActivityTime',time());
        elseif(time() - $this->session->get('lastActivityTime') > $this->timeout){

            // Log that the user is automatically logged off
            $user = Auth::user();
            $logoutlog = new \App\Logout;
            $logoutlog->user_id = $user->id;
            $logoutlog->auto = 'yes';
            $logoutlog->save();

            $this->session->forget('lastActivityTime');
            Auth::logout();
            Session::flush();
            return redirect()->route('loginpage')->with('message', 'Du ble automatisk logget ut på grunn av '.$this->timeout/60 .' minutters inaktivitet.');
        }
        $this->session->put('lastActivityTime',time());
        return $next($request);
    }

}