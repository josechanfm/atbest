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

            $failedMessage = trans('accounts_and_security.google_oauth_failed');
            if ($failedMessage === 'accounts_and_security.google_oauth_failed') {$failedMessage = 'Google 驗證失敗，請重新嘗試。';
            }

            return redirect()->route($targetRoute)
                ->withErrors(['google' => $failedMessage]);
        }

        session()->forget('google_intent');

        $googleId =$googleUser->getId();
        $googleEmail =$googleUser->getEmail();
        $googleAvatar =$googleUser->getAvatar();

        // 1. Explicit bind flow while logged in.
        if ($intent === 'bind') {
            if (! Auth::check()) {
                $expiredMessage = trans('accounts_and_security.google_session_expired');
                if ($expiredMessage === 'accounts_and_security.google_session_expired') {$expiredMessage = '登入狀態已逾時，請重新登入後再綁定 Google 帳號。';
                }

                return redirect()->route('login')
                    ->withErrors(['google' => $expiredMessage]);
            }

            /** @var User $current */$current = Auth::user();

            $existingOwner = User::where('google_id',$googleId)
                ->where('id', '!=', $current->id)
                ->first();

            if ($existingOwner) {$alreadyBoundMessage = trans('accounts_and_security.google_already_bound');
                if ($alreadyBoundMessage === 'accounts_and_security.google_already_bound') {$alreadyBoundMessage = '此 Google 帳號已綁定至其他會員帳戶。';
                }

                return redirect()->route('member.profile.index')
                    ->withErrors(['google' => $alreadyBoundMessage]);
            }

            $current->forceFill([
                'google_id' => $googleId,
                'google_email' => $googleEmail,
                'google_avatar' => $googleAvatar,
            ])->save();

            return redirect()->route('member.profile.index')
                ->with('status', trans('accounts_and_security.google_bound'));
        }

        // 2. Login flow: match strictly by bound google_id.
        $user = User::where('google_id',$googleId)->first();

        if ($user) {
            return $this->loginAndRedirect($request, $user,$intent);
        }

        // 3. Google account not yet bound -> redirect to login page with error prompt.
        $notBoundMessage = trans('accounts_and_security.google_not_bound');
        if ($notBoundMessage === 'accounts_and_security.google_not_bound') {
            $notBoundMessage = '此 Google 帳號尚未綁定帳戶，請先登入您的帳戶後再於個人帳戶頁面進行綁定。';
        }

        return redirect()->route('login')
            ->with('google_unbound_email', $googleEmail)
            ->withErrors([
                'google' => $notBoundMessage,
                'google_not_bound' => $notBoundMessage,
            ]);
    }

    protected function loginAndRedirect(Request $request, User $user, string$intent): RedirectResponse
    {
        $applocale = Session::get('applocale', config('app.locale'));
        Auth::login($user, true);$request->session()->regenerate();
        Session::put('applocale', $applocale);

        if ($intent === 'bind') {
            return redirect()->route('member.profile.index');
        }

        return $user->member
            ? redirect()->route('member.dashboard')
            : redirect()->route('guest.dashboard');
    }
}