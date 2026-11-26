<?php

use Illuminate\Support\Facades\Route;

// Member Controllers
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\ForumController as MemberForumController;
use App\Http\Controllers\Member\ForumContentController as MemberForumContentController;
use App\Http\Controllers\Member\PortfolioController as MemberPortfolioController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\Member\ProfessionalController as MemberProfessionalController;
use App\Http\Controllers\Member\MembershipController as MemberMembershipController;
use App\Http\Controllers\Member\EventController as MemberEventController;
use App\Http\Controllers\Member\EntryController as MemberEntryController;
use App\Http\Controllers\Member\BusinessCardController as MemberBusinessCardController;
use App\Http\Controllers\Member\NameCardController as MemberNamecardController;
use App\Http\Controllers\Member\InvitationController;
/*
|--------------------------------------------------------------------------
| Member Portal Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => 'member',
    'middleware' => ['auth:sanctum'],
], function () {
    Route::get('/', [MemberDashboardController::class, 'index'])->name('member.dashboard');
    Route::get('get_qrcode', [MemberDashboardController::class, 'getQrcode'])->name('member.getQrcode');

    Route::resource('forums', MemberForumController::class)->names('member.forums');
    Route::post('forum/{forum}/content/reply', [MemberForumContentController::class, 'replyContent'])->name('member.forum.content.reply');
    Route::resource('forum/{forum}/content', MemberForumContentController::class)->names('member.forum.contents');

    Route::resource('portfolios', MemberPortfolioController::class)->names('member.portfolios');
    Route::resource('profile', MemberProfileController::class)->names('member.profile');
    Route::post('profile/change_password', [MemberProfileController::class, 'changePassword'])->name('member.profile.changePassword');

    Route::resource('professionals', MemberProfessionalController::class)->names('member.professionals');

    Route::get('membership', [MemberMembershipController::class, 'index'])->name('member.membership');
    Route::post('membership/switch/{member}', [MemberMembershipController::class, 'switch'])->name('member.membership.switch');
    Route::get('membership/cards/', [MemberMembershipController::class, 'cards'])->name('member.membership.cards');

    Route::resource('events', MemberEventController::class)->names('member.events');
    Route::resource('entries', MemberEntryController::class)->names('member.entries');

    Route::get('business_card', [MemberBusinessCardController::class, 'edit'])->name('member.businessCard.edit');
    Route::put('/business_card', [MemberBusinessCardController::class, 'update'])->name('member.businessCard.update');


    Route::get('/invitations/create', [InvitationController::class, 'create'])
        ->name('member.invitations.create');
    Route::post('/invitations', [InvitationController::class, 'store'])
        ->name('member.invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])
        ->name('member.invitations.destroy');

});

Route::get('/card/{uuid}', [MemberBusinessCardController::class, 'show'])->name('member.businessCard.show');
Route::get('/namecard/{key}', [MemberNamecardController::class, 'show'])->name('member.namecard');
