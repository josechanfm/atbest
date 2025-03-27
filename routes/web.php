<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Models\Article;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//         'isMember' => Auth()->user() ? Auth()->user()->member : false,
//         'isOrganizer' => Auth()->user() ? Auth()->user()->hasRole('organizer') : false,
//         'articles' => Article::publics(),
//         'welcomeMessage'=>Article::where('category_code','WELCOME')->where('organization_id',0)->first()
//     ]);
// })->name('/');;

// Route::domain(env('APP_URL'))->group(function () {
//     Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'dashboard'])->name('/');
// });

Route::domain('{abbr}.' . env('APP_DOMAIN'))->middleware('check_subdomain')->group(function () {
    Route::get('/', [\App\Http\Controllers\WebsiteController::class, 'home'])->name('website');
    Route::get('/board', [\App\Http\Controllers\WebsiteController::class, 'board'])->name('website.board');
    Route::get('/former', [\App\Http\Controllers\WebsiteController::class, 'board'])->name('website.former');
    Route::get('/news_events', [\App\Http\Controllers\WebsiteController::class, 'newsEvents'])->name('website.newsEvents');
    Route::get('/forms', [\App\Http\Controllers\WebsiteController::class, 'forms'])->name('website.forms');
    Route::get('/article', [\App\Http\Controllers\WebsiteController::class, 'article'])->name('website.article');
});

Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'dashboard'])->name('host');
// Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'dashboard'])->name('/');

Route::get('/language/{language}', function ($language) {
    Session::put('applocale', $language);
    return Redirect::back();
})->name('language');

Route::get('registration', [\App\Http\Controllers\RegistrationController::class, 'create'])->name('registration');
Route::post('registration', [\App\Http\Controllers\RegistrationController::class, 'store'])->name('registration.store');

Route::get('article', [\App\Http\Controllers\ArticleController::class, 'item'])->name('article.item');
// Route::resource('forms', App\Http\Controllers\FormController::class)->names('forms');

Route::get('forms', [\App\Http\Controllers\FormController::class, 'index'])->name('forms');
Route::post('forms', [\App\Http\Controllers\FormController::class, 'store'])->name('forms.store');
Route::get('form', [\App\Http\Controllers\FormController::class, 'item'])->name('form.item');

Route::get('events', [App\Http\Controllers\EventController::class,'index'])->name('events');
Route::get('event', [\App\Http\Controllers\EventController::class, 'item'])->name('event.item');

Route::get('form/{entry}/receipt', [App\Http\Controllers\FormController::class, 'receipt'])->name('form.receipt');
Route::get('content', [App\Http\Controllers\ContentController::class,'page'])->name('content');

//Member
Route::group([
    'prefix' => 'member',
    'middleware' => [
        'auth:sanctum',
    ]
], function () {
    Route::get('/', [\App\Http\Controllers\Member\DashboardController::class, 'index'])->name('member.dashboard');
    Route::get('get_qrcode', [\App\Http\Controllers\Member\DashboardController::class, 'getQrcode'])->name('member.getQrcode');
    Route::resource('blogs', App\Http\Controllers\Member\BlogController::class)->names('member.blogs');
    Route::post('blog/{blog}/content/reply', [App\Http\Controllers\Member\BlogContentController::class, 'replyContent'])->name('member.blog.content.reply');
    Route::resource('blog/{blog}/content', App\Http\Controllers\Member\BlogContentController::class)->names('member.blog.contents');
    Route::resource('portfolios', App\Http\Controllers\Member\PortfolioController::class)->names('member.portfolios');
    Route::resource('profile', App\Http\Controllers\Member\ProfileController::class)->names('member.profile');
    Route::post('profile/change_password', [App\Http\Controllers\Member\ProfileController::class, 'changePassword'])->name('member.profile.changePassword');
    Route::resource('professionals', App\Http\Controllers\Member\ProfessionalController::class)->names('member.professionals');
    Route::get('membership', [App\Http\Controllers\Member\MembershipController::class, 'index'])->name('member.membership');
    Route::post('membership/switch/{member}', [App\Http\Controllers\Member\MembershipController::class, 'switch'])->name('member.membership.switch');
    Route::resource('events', App\Http\Controllers\Member\EventController::class)->names('member.events');
    Route::resource('entries', App\Http\Controllers\Member\EntryController::class)->names('member.entries');
});

//Manage
Route::group([
    'prefix' => '/organizer',
    'middleware' => [
        'auth:sanctum',
    ]
], function () {
    Route::get('/', [App\Http\Controllers\Organizer\DashboardController::class, 'index'])->name('organizer.dashboard');
    Route::post('organization/switch/{organization}', [App\Http\Controllers\Organizer\OrganizationController::class, 'switch'])->name('organizer.organization.switch');
    Route::resource('organizations', App\Http\Controllers\Organizer\OrganizationController::class)->names('organizer.organizations');
    Route::post('organization/delete_logo/{organization}', [App\Http\Controllers\Organizer\OrganizationController::class, 'deleteLogo'])->name('organizer.organization.deleteLogo');
    Route::get('/{organization}/medias', [App\Http\Controllers\Organizer\MediaController::class, 'getMedias'])->name('organizer.medias');
    Route::resource('members', App\Http\Controllers\Organizer\MemberController::class)->names('organizer.members');
    Route::post('member/create/login/{member}', [App\Http\Controllers\Organizer\MemberController::class, 'createLogin'])->name('organizer.member.createLogin');
    Route::post('member/{member}/reset_password', [\App\Http\Controllers\Organizer\MemberController::class, 'resetPassword'])->name('member.member.resetPassword');
    Route::resource('forms', App\Http\Controllers\Organizer\FormController::class)->names('organizer.forms');
    Route::delete('form/delete_image/{form}', [App\Http\Controllers\Organizer\FormController::class, 'deleteImage'])->name('organizer.form.deleteImage');
    Route::post('form/{form}/backup', [App\Http\Controllers\Organizer\FormController::class, 'backup'])->name('organizer.form.backup');
    Route::resource('form/{form}/fields', App\Http\Controllers\Organizer\FormFieldController::class)->names('organizer.form.fields');
    Route::post('form/{form}/fields_sequence', [App\Http\Controllers\Organizer\FormFieldController::class, 'fieldsSequence'])->name('organizer.form.fieldsSequence');
    Route::resource('form/{form}/entries', App\Http\Controllers\Organizer\EntryController::class)->names('organizer.form.entries');
    Route::get('form/{form}/entry/{entry}/success', [App\Http\Controllers\Organizer\EntryController::class, 'success'])->name('organizer.form.entry.success');
    Route::post('form/{form}/createEventAttendees', [App\Http\Controllers\Organizer\FormController::class, 'createEventAttendees'])->name('organizer.form.createEventAttendees');
    Route::get('member/export', [App\Http\Controllers\Organizer\MemberController::class, 'export'])->name('member.member.export');
    Route::get('entry/{form}/export', [App\Http\Controllers\Organizer\EntryController::class, 'export'])->name('organizer.entry.export');
    Route::resource('approbates', App\Http\Controllers\Organizer\ApprobateController::class)->names('organizer.approbates');
    Route::resource('bulletins', App\Http\Controllers\Organizer\BulletinController::class)->names('organizer.bulletins');
    Route::resource('messages', App\Http\Controllers\Organizer\MessageController::class)->names('organizer.messages');
    Route::resource('certificates', App\Http\Controllers\Organizer\CertificateController::class)->names('organizer.certificates');
    Route::get('certificates/delete_media/{mediaId}', [App\Http\Controllers\Organizer\CertificateController::class, 'deleteMedia'])->name('organizer.certificate.deleteMedia');
    Route::resource('certificate/{certificate}/members', App\Http\Controllers\Organizer\CertificateMemberController::class)->names('organizer.certificate.members');
    Route::resource('emails', App\Http\Controllers\Organizer\EmailController::class)->names('organizer.emails');
    Route::resource('blogs', App\Http\Controllers\Organizer\BlogController::class)->names('organizer.blogs');
    Route::resource('blog-contents', App\Http\Controllers\Organizer\BlogContentController::class)->names('organizer.blog.contents');
    Route::resource('articles', App\Http\Controllers\Organizer\ArticleController::class)->names('organizer.articles');
    Route::delete('article/delete_image/{article}', [App\Http\Controllers\Organizer\ArticleController::class, 'deleteImage'])->name('organizer.article.deleteImage');
    Route::resource('events', App\Http\Controllers\Organizer\EventController::class)->names('organizer.events');
    Route::delete('event/delete_image/{event}', [App\Http\Controllers\Organizer\EventController::class, 'deleteImage'])->name('organizer.event.deleteImage');
    Route::resource('configs', App\Http\Controllers\Organizer\ConfigController::class)->names('organizer.configs');
    Route::get('image_upload', [App\Http\Controllers\Organizer\UploaderController::class, 'image'])->name('organizer.image_upload');

});

//admin
Route::group([
    'prefix' => '/admin',
    'middleware' => [
        'auth:sanctum',
        'role:admin'
    ]
], function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('organizations', App\Http\Controllers\Admin\OrganizationController::class)->names('admin.organizations');
    Route::get('organization/{organization}/members', [App\Http\Controllers\Admin\OrganizationController::class, 'members'])->name('admin.organization.members');
    Route::post('organization/masquerade/{organization}', [App\Http\Controllers\Admin\OrganizationController::class, 'masquerade'])->name('admin.organization.masquerade');

    Route::resource('members', App\Http\Controllers\Admin\MemberController::class)->names('admin.members');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->names('admin.users');
    Route::resource('configs', App\Http\Controllers\Admin\ConfigController::class)->names('admin.configs');
    Route::resource('features', App\Http\Controllers\Admin\FeatureController::class)->names('admin.features');
    Route::post('feature/delete_image/{feature}', [App\Http\Controllers\Admin\FeatureController::class, 'deleteImage'])->name('admin.feature.deleteImage');
    Route::resource('articles', App\Http\Controllers\Admin\ArticleController::class)->names('admin.articles');
    Route::post('article/sequence', [App\Http\Controllers\Admin\ArticleController::class, 'sequence'])->name('admin.article.sequence');
    Route::post('article/delete_image/{article}', [App\Http\Controllers\Admin\ArticleController::class, 'deleteImage'])->name('admin.article.deleteImage');
    Route::resource('issues', App\Http\Controllers\Admin\IssueController::class)->names('admin.issues');

    Route::resource('forms', App\Http\Controllers\Admin\FormController::class)->names('admin.forms');
    Route::resource('form/{form}/fields', App\Http\Controllers\Admin\FormFieldController::class)->names('admin.form.fields');
    Route::resource('form/{form}/entries', App\Http\Controllers\Admin\EntryController::class)->names('admin.form.entries');
    Route::get('entry/{form}/export', [App\Http\Controllers\Admin\EntryController::class, 'export'])->name('admin.entry.export');
    Route::get('form/{form}/entry/{entry}/success', [App\Http\Controllers\Admin\EntryController::class, 'success'])->name('admin.form.entry.success');
    Route::post('form/delete_media/{form}', [App\Http\Controllers\Admin\FormController::class, 'deleteMedia'])->name('admin.form.deleteMedia');

});







//Widget
Route::group([
    'prefix' => 'widget',
], function () {
    Route::get('polling',[App\Http\Controllers\Widget\PollController::class,'polling'])->name('widget.polling');
    Route::post('poll/vote',[App\Http\Controllers\Widget\PollController::class,'vote'])->name('widget.poll.vote');
    
    //Widget Admin
    Route::group([
        'prefix' => 'admin',
        'middleware' => [
            'auth:sanctum',
            'role:organizer|admin'
        ]
    ], function () {
        Route::get('/',[App\Http\Controllers\Widget\Admin\DashboardController::class, 'index'])->name('widget.admin.dashboard');
        Route::resource('polls',App\Http\Controllers\Widget\Admin\PollController::class)->names('widget.admin.polls');
        Route::post('poll/{poll}/responseClear',[App\Http\Controllers\Widget\Admin\PollController::class,'responseClear'])->name('widget.admin.poll.responseClear');
        Route::post('poll/{poll}/responseAll',[App\Http\Controllers\Widget\Admin\PollController::class,'responseAll'])->name('widget.admin.poll.responseAll');
        
        Route::get('materials', function(){
            return Inertia::render('Widget/ComingSoon');
        })->name('widget.admin.materials');
        Route::get('tutorials', function(){
            return Inertia::render('Widget/ComingSoon');
        })->name('widget.admin.tutorials');
    });


});
require __DIR__ . '/auth.php';
