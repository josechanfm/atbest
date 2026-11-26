<?php

use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\Organizer\ArticleController as OrganizerArticleController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\ConfigController as OrganizerConfigController;
use App\Http\Controllers\Organizer\UploaderController as OrganizerUploaderController;
use App\Http\Controllers\Organizer\ForumController as OrganizerForumController;
use App\Http\Controllers\Organizer\ForumContentController as OrganizerForumContentController;

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
    Route::resource('forums', OrganizerForumController::class)->names('organizer.forums');
    Route::resource('forum-contents', OrganizerForumContentController::class)->names('organizer.forum.contents');

    Route::resource('articles', OrganizerArticleController::class)->names('organizer.articles');
    Route::delete('article/delete_image/{article}', [OrganizerArticleController::class, 'deleteImage'])->name('organizer.article.deleteImage');

    Route::resource('events', OrganizerEventController::class)->names('organizer.events');
    Route::delete('event/delete_image/{event}', [OrganizerEventController::class, 'deleteImage'])->name('organizer.event.deleteImage');

    Route::resource('configs', OrganizerConfigController::class)->names('organizer.configs');
    Route::get('image_upload', [OrganizerUploaderController::class, 'image'])->name('organizer.image_upload');
});
