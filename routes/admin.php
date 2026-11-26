<?php

use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ConfigController as AdminConfigController;
use App\Http\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\ForumController as AdminForumController;
use App\Http\Controllers\Admin\IssueController as AdminIssueController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\EntryController as AdminEntryController;
use App\Http\Controllers\Admin\SmsMessageController as AdminSmsMessageController;
use App\Http\Controllers\Admin\MessageTestController as AdminMessageTestController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\JoinRequestController as AdminJoinRequestController;

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

    Route::resource('forums', AdminForumController::class)->names('admin.forums');

    Route::resource('issues', AdminIssueController::class)->names('admin.issues');

    Route::resource('forms', AdminFormController::class)->names('admin.forms');
    Route::resource('form/{form}/fields', AdminFormFieldController::class)->names('admin.form.fields');
    Route::resource('form/{form}/entries', AdminEntryController::class)->names('admin.form.entries');
    Route::get('entry/{form}/export', [AdminEntryController::class, 'export'])->name('admin.entry.export');
    Route::get('form/{form}/entry/{entry}/success', [AdminEntryController::class, 'success'])->name('admin.form.entry.success');
    Route::post('form/delete_media/{form}', [AdminFormController::class, 'deleteMedia'])->name('admin.form.deleteMedia');

    Route::resource('sms_messages', AdminSmsMessageController::class)->names('admin.smsMessages');

    Route::resource('enquiries', AdminEnquiryController::class)->names('admin.enquiries');
    Route::post('enquiry/{enquiry}/read', [AdminEnquiryController::class, 'markRead'])->name('admin.enquiries.read');

    Route::get('join_requests', [AdminJoinRequestController::class, 'index'])->name('admin.joinRequests.index');
    Route::post('join_request/{joinRequest}/approve', [AdminJoinRequestController::class, 'approve'])->name('admin.joinRequests.approve');
    Route::post('join_request/{joinRequest}/reject', [AdminJoinRequestController::class, 'reject'])->name('admin.joinRequests.reject');

    Route::get('message_test', [AdminMessageTestController::class, 'index'])->name('admin.messageTest.index');
    Route::get('message_test/health', [AdminMessageTestController::class, 'health'])->name('admin.messageTest.health');
    Route::post('message_test/email', [AdminMessageTestController::class, 'sendTestEmail'])->name('admin.messageTest.sendEmail');
    Route::post('message_test/sms', [AdminMessageTestController::class, 'sendTestSms'])->name('admin.messageTest.sendSms');
});
