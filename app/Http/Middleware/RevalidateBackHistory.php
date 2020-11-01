<?php

namespace App\Http\Middleware;

use Closure;

class RevalidateBackHistory
{
    /**CUSTOM MIDDLEWARE TO PREVENT USER FROM ACCESSING INFO BY
     *CLICKING "BACK" IN THE BROWSER AFTER LOGOUT
     *
     *Used by adding middleware "revalidate"
     *
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin' , '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
        $response->headers->set('Cache-Control' , 'nocache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma' , 'no-cache');
        $response->headers->set('Expires' , 'Fri, 01 Jan 1990 00:00:00 GMT');
        
        return $response;
    }
}