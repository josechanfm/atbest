<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;
        
        foreach ($guards as $guard) {
            // 只檢查 'web' guard
            if ($guard === 'web' && Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                // 根據角色跳轉到不同頁面
                return $this->redirectBasedOnRole($user->roles[0]);
            }
            
            // 其他 guard 的處理（保持原樣）
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }

    protected function redirectBasedOnRole($role): Response
    {   
        switch ($role->name) {
            case 'admin':
                return redirect()->route('admin.dashboard'); // 假設你有命名路由
            case 'master':
                return redirect()->route('master.dashboard');
            case 'member':
                return redirect()->route('member.dashboard');
            case 'organizer':
                return redirect()->route('organizer.dashboard');
            default:
                return redirect(RouteServiceProvider::HOME);
        }
    }
}
