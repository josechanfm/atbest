<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Session;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        // dd($request->session()->get('applocale'));
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'organization' => fn() => $request->session()->get('organization')
            ],
            'lang' => $request->session()->get('applocale')
            // 'ziggy' => fn () => [
            //     ...(new Ziggy)->toArray(),
            //     'location' => $request->url(),
            // ],
        ];
    }
}
