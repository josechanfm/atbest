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
use App\Http\Controllers\NamecardController;

// Guest Controllers
use App\Http\Controllers\Guest\DashboardController as GuestDashboardController;

// Member Controllers
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\BlogController as MemberBlogController;
use App\Http\Controllers\Member\BlogContentController as MemberBlogContentController;
use App\Http\Controllers\Member\PortfolioController as MemberPortfolioController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\Member\ProfessionalController as MemberProfessionalController;
use App\Http\Controllers\Member\MembershipController as MemberMembershipController;
use App\Http\Controllers\Member\EventController as MemberEventController;
use App\Http\Controllers\Member\EntryController as MemberEntryController;
use App\Http\Controllers\Member\BusinessCardController as MemberBusinessCardController;

// Organizer Controllers
use App\Http\Controllers\Organizer\DashboardController as OrganizerDashboardController;
use App\Http\Controllers\Organizer\OrganizationController as OrganizerOrganizationController;
use App\Http\Controllers\Organizer\MediaController as OrganizerMediaController;
use App\Http\Controllers\Organizer\MemberController as OrganizerMemberController;
use App\Http\Controllers\Organizer\FormController as OrganizerFormController;
use App\Http\Controllers\Organizer\FormFieldController as OrganizerFormFieldController;
use App\Http\Controllers\Organizer\EntryController as OrganizerEntryController;
use App\Http\Controllers\Organizer\ApprobateController as OrganizerApprobateController;
use App\Http\Controllers\Organizer\BulletinController as OrganizerBulletinController;
use App\Http\Controllers\Organizer\MessageController as OrganizerMessageController;
use App\Http\Controllers\Organizer\CertificateController as OrganizerCertificateController;
use App\Http\Controllers\Organizer\CertificateMemberController as OrganizerCertificateMemberController;
use App\Http\Controllers\Organizer\EmailController as OrganizerEmailController;
use App\Http\Controllers\Organizer\BlogController as OrganizerBlogController;
use App\Http\Controllers\Organizer\BlogContentController as OrganizerBlogContentController;
use App\Http\Controllers\Organizer\ArticleController as OrganizerArticleController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\ConfigController as OrganizerConfigController;
use App\Http\Controllers\Organizer\UploaderController as OrganizerUploaderController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ConfigController as AdminConfigController;
use App\Http\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\IssueController as AdminIssueController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\EntryController as AdminEntryController;
use App\Http\Controllers\Admin\SmsMessageController as AdminSmsMessageController;

// Widget Controllers
use App\Http\Controllers\Widget\PollController as WidgetPollController;
use App\Http\Controllers\Widget\Admin\DashboardController as WidgetAdminDashboardController;
use App\Http\Controllers\Widget\Admin\PollController as WidgetAdminPollController;

// Registration Inviation Controllers
use App\Http\Controllers\Auth\InvitationRegisterController;
use App\Http\Controllers\Member\InvitationController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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

Route::get('/card/{uuid}', [MemberBusinessCardController::class, 'show'])->name('member.businessCard.show');
Route::get('/namecard/{key}', [NamecardController::class, 'show'])->name('member.namecard');

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
    
    Route::resource('blogs', MemberBlogController::class)->names('member.blogs');
    Route::post('blog/{blog}/content/reply', [MemberBlogContentController::class, 'replyContent'])->name('member.blog.content.reply');
    Route::resource('blog/{blog}/content', MemberBlogContentController::class)->names('member.blog.contents');
    
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

/*
|--------------------------------------------------------------------------
| Organizer Portal Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => '/organizer',
    'middleware' => ['auth:sanctum'],
], function () {
    Route::get('/', [OrganizerDashboardController::class, 'index'])->name('organizer.dashboard');
    
    Route::post('organization/switch/{organization}', [OrganizerOrganizationController::class, 'switch'])->name('organizer.organization.switch');
    Route::resource('organizations', OrganizerOrganizationController::class)->names('organizer.organizations');
    Route::post('organization/delete_logo/{organization}', [OrganizerOrganizationController::class, 'deleteLogo'])->name('organizer.organization.deleteLogo');
    Route::get('/{organization}/medias', [OrganizerMediaController::class, 'getMedias'])->name('organizer.medias');
    
    Route::post('members/import', [OrganizerMemberController::class, 'import'])->name('organizer.members.import');
    Route::resource('members', OrganizerMemberController::class)->names('organizer.members');
    Route::post('member/create/login/{member}', [OrganizerMemberController::class, 'createLogin'])->name('organizer.member.createLogin');
    Route::post('member/{member}/reset_password', [OrganizerMemberController::class, 'resetPassword'])->name('member.member.resetPassword');
    Route::get('member/export', [OrganizerMemberController::class, 'export'])->name('member.member.export');
    
    Route::resource('forms', OrganizerFormController::class)->names('organizer.forms');
    Route::post('form/{form}/clone', [OrganizerFormController::class, 'clone'])->name('manage.form.clone');
    Route::delete('form/delete_image/{form}', [OrganizerFormController::class, 'deleteImage'])->name('organizer.form.deleteImage');
    Route::post('form/{form}/backup', [OrganizerFormController::class, 'backup'])->name('organizer.form.backup');
    Route::resource('form/{form}/fields', OrganizerFormFieldController::class)->names('organizer.form.fields');
    Route::post('form/{form}/fields_sequence', [OrganizerFormFieldController::class, 'fieldsSequence'])->name('organizer.form.fieldsSequence');
    Route::resource('form/{form}/entries', OrganizerEntryController::class)->names('organizer.form.entries');
    Route::get('form/{form}/entry/{entry}/success', [OrganizerEntryController::class, 'success'])->name('organizer.form.entry.success');
    Route::post('form/{form}/createEventAttendees', [OrganizerFormController::class, 'createEventAttendees'])->name('organizer.form.createEventAttendees');
    Route::get('entry/{form}/export', [OrganizerEntryController::class, 'export'])->name('organizer.entry.export');
    
    Route::resource('approbates', OrganizerApprobateController::class)->names('organizer.approbates');
    Route::resource('bulletins', OrganizerBulletinController::class)->names('organizer.bulletins');
    Route::resource('messages', OrganizerMessageController::class)->names('organizer.messages');
    
    Route::resource('certificates', OrganizerCertificateController::class)->names('organizer.certificates');
    Route::get('certificates/delete_media/{mediaId}', [OrganizerCertificateController::class, 'deleteMedia'])->name('organizer.certificate.deleteMedia');
    Route::resource('certificate/{certificate}/members', OrganizerCertificateMemberController::class)->names('organizer.certificate.members');
    
    Route::resource('emails', OrganizerEmailController::class)->names('organizer.emails');
    Route::resource('blogs', OrganizerBlogController::class)->names('organizer.blogs');
    Route::resource('blog-contents', OrganizerBlogContentController::class)->names('organizer.blog.contents');
    
    Route::resource('articles', OrganizerArticleController::class)->names('organizer.articles');
    Route::delete('article/delete_image/{article}', [OrganizerArticleController::class, 'deleteImage'])->name('organizer.article.deleteImage');
    
    Route::resource('events', OrganizerEventController::class)->names('organizer.events');
    Route::delete('event/delete_image/{event}', [OrganizerEventController::class, 'deleteImage'])->name('organizer.event.deleteImage');
    
    Route::resource('configs', OrganizerConfigController::class)->names('organizer.configs');
    Route::get('image_upload', [OrganizerUploaderController::class, 'image'])->name('organizer.image_upload');
});

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => '/admin',
    'middleware' => ['auth:sanctum', 'role:admin'],
], function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    Route::resource('organizations', AdminOrganizationController::class)->names('admin.organizations');
    Route::get('organization/{organization}/members', [AdminOrganizationController::class, 'members'])->name('admin.organization.members');
    Route::post('organization/masquerade/{organization}', [AdminOrganizationController::class, 'masquerade'])->name('admin.organization.masquerade');

    Route::resource('members', AdminMemberController::class)->names('admin.members');
    Route::resource('users', AdminUserController::class)->names('admin.users');
    Route::resource('configs', AdminConfigController::class)->names('admin.configs');
    
    Route::resource('features', AdminFeatureController::class)->names('admin.features');
    Route::post('feature/delete_image/{feature}', [AdminFeatureController::class, 'deleteImage'])->name('admin.feature.deleteImage');
    
    Route::resource('articles', AdminArticleController::class)->names('admin.articles');
    Route::post('article/sequence', [AdminArticleController::class, 'sequence'])->name('admin.article.sequence');
    Route::post('article/delete_image/{article}', [AdminArticleController::class, 'deleteImage'])->name('admin.article.deleteImage');
    
    Route::resource('issues', AdminIssueController::class)->names('admin.issues');

    Route::resource('forms', AdminFormController::class)->names('admin.forms');
    Route::resource('form/{form}/fields', AdminFormFieldController::class)->names('admin.form.fields');
    Route::resource('form/{form}/entries', AdminEntryController::class)->names('admin.form.entries');
    Route::get('entry/{form}/export', [AdminEntryController::class, 'export'])->name('admin.entry.export');
    Route::get('form/{form}/entry/{entry}/success', [AdminEntryController::class, 'success'])->name('admin.form.entry.success');
    Route::post('form/delete_media/{form}', [AdminFormController::class, 'deleteMedia'])->name('admin.form.deleteMedia');

    Route::resource('sms_messages', AdminSmsMessageController::class)->names('admin.smsMessages');
});

/*
|--------------------------------------------------------------------------
| Widget Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix' => 'widget',
], function () {
    Route::get('polling', [WidgetPollController::class, 'polling'])->name('widget.polling');
    Route::post('poll/vote', [WidgetPollController::class, 'vote'])->name('widget.poll.vote');
    
    // Widget Admin
    Route::group([
        'prefix' => 'admin',
        'middleware' => ['auth:sanctum', 'role:organizer|admin'],
    ], function () {
        Route::get('/', [WidgetAdminDashboardController::class, 'index'])->name('widget.admin.dashboard');
        Route::resource('polls', WidgetAdminPollController::class)->names('widget.admin.polls');
        Route::post('poll/{poll}/responseClear', [WidgetAdminPollController::class, 'responseClear'])->name('widget.admin.poll.responseClear');
        Route::post('poll/{poll}/responseAll', [WidgetAdminPollController::class, 'responseAll'])->name('widget.admin.poll.responseAll');
        
        Route::get('materials', function () {
            return Inertia::render('Widget/ComingSoon');
        })->name('widget.admin.materials');
        
        Route::get('tutorials', function () {
            return Inertia::render('Widget/ComingSoon');
        })->name('widget.admin.tutorials');
    });
});



// Public — invitation landing & registration
Route::get('/invitations/{token}', [InvitationRegisterController::class, 'show'])
    ->name('invitations.show');
Route::post('/invitations/{token}/register', [InvitationRegisterController::class, 'store'])
    ->name('invitations.register');

require __DIR__ . '/auth.php';