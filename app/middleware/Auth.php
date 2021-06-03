<?php
namespace Middleware;

/**
 * Auth class
 * 
 */ 
class Auth
{
    /**
     * Handle response 
     * 
     * @param \Kernel\Http\Request $request
     * @param Closure $next
     * 
     * @return \Kernel\Http\Response
     */
    public function handle($request, \Closure $next)
    {
        $response =  $next($request);

        return $response;
    }
}
