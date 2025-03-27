<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = Session::get('applocale', config('app.locale'));
        //App::setLocale($locale);


                // // Check if a language is set in the session or request
                // if ($request->session()->has('applocale')) {
                //     App::setLocale($request->session()->get('applocale'));
                // }


        return $next($request);
    }
}