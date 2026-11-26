<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

// General & Public Controllers
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\EnquiryController;

// Guest Controllers
use App\Http\Controllers\Guest\DashboardController as GuestDashboardController;

// Registration Inviation Controllers
use App\Http\Controllers\Auth\InvitationRegisterController;

/*
|--------------------------------------------------------------------------
| Subdomain Routes
|--------------------------------------------------------------------------
*/
Route::domain('{abbr}.' . env('APP_DOMAIN'))->middleware('check_subdomain')->group(function () {
    Route::get('/', [WebsiteController::class, 'home'])->name('website');
    Route::get('/board', [WebsiteController::class, 'board'])->name('website.board');
    Route::get('/former', [WebsiteController::class, 'board'])->name('website.former');
    Route::get('/news_events', [WebsiteController::class, 'newsEvents'])->name('website.newsEvents');
    Route::get('/forms', [WebsiteController::class, 'forms'])->name('website.forms');
    Route::get('/article', [WebsiteController::class, 'article'])->name('website.article');
});

/*
|--------------------------------------------------------------------------
| Public & General Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [WelcomeController::class, 'dashboard'])->name('home');
Route::get('/getRss', [WelcomeController::class, 'getRss'])->name('host.getRss');

Route::get('/language/{language}', function ($language) {
    Session::put('applocale', $language);
    return Redirect::back();
})->name('language');

Route::get('article', [ArticleController::class, 'item'])->name('article.item');

Route::get('forms', [FormController::class, 'index'])->name('forms');
Route::post('forms', [FormController::class, 'store'])->name('forms.store');
Route::get('form', [FormController::class, 'item'])->name('form.item');
Route::get('form/{entry}/receipt', [FormController::class, 'receipt'])->name('form.receipt');

Route::get('events', [EventController::class, 'index'])->name('events');
Route::get('event', [EventController::class, 'item'])->name('event.item');

Route::get('content', [ContentController::class, 'page'])->name('content');

Route::get('/system-intro', fn () => Inertia::render('SystemIntro', [
    'recaptchaSiteKey' => config('services.recaptcha.site_key'),
]))->name('system.intro');
Route::get('/privacy-policy', fn () => Inertia::render('Privacy'))->name('privacy.policy');

Route::post('enquiry', [EnquiryController::class, 'store'])->name('enquiry.store');


/*
|--------------------------------------------------------------------------
| Guest Portal Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => 'guest',
    'middleware' => ['auth:sanctum'],
], function () {
    Route::get('/', [GuestDashboardController::class, 'index'])->name('guest.dashboard');
});


// Public — invitation landing & registration
Route::get('/invitations/{token}', [InvitationRegisterController::class, 'show'])
    ->name('invitations.show');
Route::post('/invitations/{token}/send-code', [InvitationRegisterController::class, 'sendCode'])
    ->name('invitations.sendCode');
Route::post('/invitations/{token}/verify-code', [InvitationRegisterController::class, 'verifyCode'])
    ->name('invitations.verifyCode');
Route::post('/invitations/{token}/register', [InvitationRegisterController::class, 'register'])
    ->name('invitations.register');

require __DIR__ . '/auth.php';
require __DIR__ . '/member.php';
require __DIR__ . '/organizer.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/widget.php';
