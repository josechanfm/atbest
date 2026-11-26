<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        $intent = Route::currentRouteName() === 'auth.google.bind' ? 'bind' : 'login';

        session(['google_intent' => $intent]);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $intent = session('google_intent', 'login');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            session()->forget('google_intent');
            Log::warning('Google OAuth callback failed', [
                'intent' => $intent,
                'error'  => $e->getMessage(),
            ]);

            $targetRoute = ($intent === 'bind' && Auth::check())
                ? 'member.profile.index'
                : 'login';

            return redirect()->route($targetRoute)
                ->withErrors(['google' => 'google_oauth_failed']);
        }

        session()->forget('google_intent');

        $googleId = $googleUser->getId();
        $googleEmail = $googleUser->getEmail();
        $googleAvatar = $googleUser->getAvatar();

        // 1. Explicit bind flow while logged in.
        if ($intent === 'bind') {
            if (! Auth::check()) {
                return redirect()->route('login')
                    ->withErrors(['google' => 'google_session_expired']);
            }

            /** @var User $current */
            $current = Auth::user();

            $existingOwner = User::where('google_id', $googleId)
                ->where('id', '!=', $current->id)
                ->first();

            if ($existingOwner) {
                return redirect()->route('member.profile.index')
                    ->withErrors(['google' => 'google_already_bound']);
            }

            $current->forceFill([
                'google_id' => $googleId,
                'google_email' => $googleEmail,
                'google_avatar' => $googleAvatar,
            ])->save();

            return redirect()->route('member.profile.index')
                ->with('status', 'google_bound');
        }

        // 2. Login flow: look up by google_id first.
        $user = User::where('google_id', $googleId)->first();

        // 3. Login flow: auto-bind if email matches an existing user without a conflicting google_id.
        if (! $user && $googleEmail) {
            $user = User::where('email', $googleEmail)->first();
            if ($user) {
                $user->forceFill([
                    'google_id' => $googleId,
                    'google_email' => $googleEmail,
                    'google_avatar' => $googleAvatar,
                ])->save();
            }
        }

        if ($user) {
            return $this->loginAndRedirect($request, $user, $intent);
        }

        // 4. Login flow: google_id not found on users table -> prompt on login page.
        $prompt = '此 Google 帳號尚未綁定系統帳戶。請先完成登入，再於會員／帳戶頁面綁定 Google 帳號。';

        return redirect()->route('login')
            ->with('google_unbound_email', $googleEmail)
            ->withErrors(['google' => $prompt]);
    }

    protected function loginAndRedirect(Request $request, User $user, string $intent): RedirectResponse
    {
        $applocale = Session::get('applocale', config('app.locale'));
        Auth::login($user, true);
        $request->session()->regenerate();
        Session::put('applocale', $applocale);

        if ($intent === 'bind') {
            return redirect()->route('member.profile.index');
        }

        return $user->member
            ? redirect()->route('member.dashboard')
            : redirect()->route('guest.dashboard');
    }
}