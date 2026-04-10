<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminQueueController;
use App\Http\Controllers\PublicQueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicQueueController::class, 'create'])->name('queue.create');
Route::post('/queues', [PublicQueueController::class, 'store'])->name('queue.store');
Route::get('/queues/{queue}/success', [PublicQueueController::class, 'success'])->name('queue.success');
Route::get('/queues/board', [PublicQueueController::class, 'board'])->name('queue.board');
Route::get('/queues/{queue}/status', [PublicQueueController::class, 'ticketStatus'])->name('queue.status');

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminQueueController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live', [AdminQueueController::class, 'live'])->name('dashboard.live');
    Route::get('/profile', [AdminAuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AdminAuthController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/queues/call-next', [AdminQueueController::class, 'callNext'])->name('queues.call-next');
    Route::post('/queues/{queue}/call', [AdminQueueController::class, 'call'])->name('queues.call');
    Route::post('/queues/{queue}/start', [AdminQueueController::class, 'start'])->name('queues.start');
    Route::post('/queues/{queue}/finish', [AdminQueueController::class, 'finish'])->name('queues.finish');
    Route::post('/queues/{queue}/cancel', [AdminQueueController::class, 'cancel'])->name('queues.cancel');
    Route::delete('/queues/{queue}', [AdminQueueController::class, 'destroy'])->name('queues.destroy');
});
