<?php

use Illuminate\Support\Facades\Route;

// Widget Controllers
use App\Http\Controllers\Widget\PollController as WidgetPollController;
use App\Http\Controllers\Widget\Admin\DashboardController as WidgetAdminDashboardController;
use App\Http\Controllers\Widget\Admin\PollController as WidgetAdminPollController;

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