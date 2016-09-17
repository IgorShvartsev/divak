<?php

namespace Kernel\Http\Middleware;

use \Kernel\Exception\ResponseException;

/**
*  InitTranslator class
*  Middleware
*/
class InitTranslator
{   
    /**
    *  Middleware handle function
    *  Is called by Kernel
    *
    *  @param \Kernel\Http\Request $request
    *  @param \Closure $next
    */
	public function handle($request, \Closure $next)
	{
		$lang = $request->getParam('lang');
		
		$response = $next($request);
        if (in_array(strtolower($lang), \Config::get('app.all_langs')) 
            || in_array(strtoupper($lang), \Config::get('app.all_langs'))) {
                $translator = new \Translator(
                    APP_PATH . '/languages', 
                    strtoupper($lang) 
                );

               \App::bindInstance(\Translator::class, $translator);
               
                // very important to enable that to have oportunity to process non-english text 
                // by PHP multibyte string functions 
                mb_internal_encoding( 'UTF-8' );
        } else {
            $response->responseCodeHeader(404);
            \View::quickRender('error', ['description' => 'Page Not Found']);
            exit();
        } 
        return $response;
	}
}