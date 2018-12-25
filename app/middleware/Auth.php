<?php
namespace Middleware;

class Auth
{
    public function handle($response, \Closure $next)
    {
        $response =  $next($response);
        
        return $response;
    }
}
