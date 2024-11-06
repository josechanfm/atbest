<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;

class CheckSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $subdomain = $request->route()->parameter('abbr');
        // Check if the subdomain exists
        $organization = Organization::where('subdomain', $subdomain)->first();
        //dd($subdomain, $organization);
        if ($organization) {
            $request->merge(['organization' => $organization]);
            return $next($request);
        }

        // Redirect to the main domain if the subdomain doesn't exist
        return redirect()->to(config('app.url'));        
    }
}
