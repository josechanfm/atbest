<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // $locale = Session::get('applocale', config('app.locale'));
        
        // dd( config('app.locale') );
        // dd( __('login'));
        // Session::get('applocale');
        // Session::put('applocale', $locale);

        // App::setLocale($locale);


        // // Check if a language is set in the session or request
        if ($request->session()->has('applocale')) {
            // 有session行session
            $locale = $request->session()->get('applocale');
        }else{
            // 冇session行default
            $locale = config('app.locale');
        }

        App::setLocale( $locale );
        Session::put('applocale', $locale);

        return $next($request);
    }
}