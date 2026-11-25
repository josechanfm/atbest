<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     *
     * The intent (login or bind) is derived from the current route name and
     * stored in the session so the callback knows how to handle the account.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        $intent = Route::currentRouteName() === 'auth.google.bind' ? 'bind' : 'login';

        session(['google_intent' => $intent]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $intent = session('google_intent', 'login');
        session()->forget('google_intent');

        $googleUser = Socialite::driver('google')->user();
        $googleId = $googleUser->getId();
        $googleEmail = $googleUser->getEmail();
        $googleAvatar = $googleUser->getAvatar();

        // 1. Already bound to an account by google_id.
        $user = User::where('google_id', $googleId)->first();

        // 2. Google email matches an existing account -> auto-bind.
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

        // 3. Bind intent while authenticated -> bind to the current user.
        if ($intent === 'bind') {
            if (Auth::check()) {
                $current = Auth::user();
                $current->forceFill([
                    'google_id' => $googleId,
                    'google_email' => $googleEmail,
                    'google_avatar' => $googleAvatar,
                ])->save();

                return redirect()->route('member.profile.index')
                    ->with('status', trans('accounts_and_security.google_bound'));
            }

            // Session expired mid-flow -> send back to login.
            return redirect()->route('login');
        }

        // 4. Login intent with no matching account -> not bound.
        return redirect()->route('login')
            ->withErrors(['email' => trans('accounts_and_security.google_not_bound')]);
    }

    /**
     * Log the user in and redirect to the appropriate portal.
     */
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