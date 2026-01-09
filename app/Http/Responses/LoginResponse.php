<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user(); // Jetstream/Fortify 登入後，這裡一定有 user（web guard）

        // 依角色分流（建議用這個，不建議用多 guard）
        $roleName = optional($user->roles)->first()?->name;

        $redirect = match ($roleName) {
            'admin'     => route('admin.dashboard'),
            'master'    => route('master.dashboard'),
            'member'    => route('member.dashboard'),
            'organizer' => route('organizer.dashboard'),
            default     => config('fortify.home'),
        };

        return redirect()->intended($redirect);
    }
}