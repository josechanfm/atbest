<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\MobilePasswordController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterUserController::class, 'create'])
        ->name('register');
    Route::post('/register/pre-register', [RegisterUserController::class, 'preRegister'])
        ->name('register.preRegister');
    Route::post('/register/verify-code', [RegisterUserController::class, 'verifyCode'])
        ->name('register.verifyCode');        
    Route::post('register', [RegisterUserController::class, 'store'])
        ->name('register.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    


    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //     ->name('password.request');

    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //     ->name('password.email');

    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    //     ->name('password.reset');

    // Route::post('reset-password', [NewPasswordController::class, 'store'])
    //     ->name('password.store');
});



        Route::post('mobile-forgot-password/send', [MobilePasswordController::class, 'sendResetCode'])
            ->name('mobileForgotPassword.send');
        Route::post('mobile-forgot-password/verify', [MobilePasswordController::class, 'verifyCode'])
            ->name('mobileForgotPassword.verify');

    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');

    Route::middleware('auth')->group(function () {
        Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
        Route::get('auth/google/bind', [GoogleController::class, 'redirectToGoogle'])->middleware('auth:sanctum')->name('auth.google.bind');
        // Route::get('mobile-forgot-password', [MobilePasswordController::class, 'forgotPassword'])->name('mobileForgotPassword');
        // Route::post('mobile-forgot-password', [MobilePasswordController::class, 'sendValidation'])->name('mobileSendValidation');
        // Route::post('mobile-validate-code', [MobilePasswordController::class, 'validateCode'])->name('mobileValidateCode');
        Route::get('mobile-forgot-password', [MobilePasswordController::class, 'showResetForm'])
            ->name('mobileForgotPassword.form');
        Route::post('mobile-forgot-password/reset', [MobilePasswordController::class, 'resetPassword'])
            ->name('mobileForgotPassword.reset');

        // Route::get('verify-email', EmailVerificationPromptController::class)
        //     ->name('verification.notice');
    
        // Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        //     ->middleware(['signed', 'throttle:6,1'])
        //     ->name('verification.verify');
    
        // Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        //     ->middleware('throttle:6,1')
        //     ->name('verification.send');
    
        // Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        //     ->name('password.confirm');
    
        // Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    
        // Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    
        Route::post('logout', [AuthenticatedSessionController::class, 'logout'])
            ->name('logout');
    });
