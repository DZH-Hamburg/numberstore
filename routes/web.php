<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElementController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/invitations/{token}', [InvitationAcceptController::class, 'show'])
    ->name('invitations.show');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('users', AdminUserController::class)->except(['show']);
    });

    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

    Route::post('/groups/{group}/invitations', [GroupInvitationController::class, 'store'])->name('groups.invitations.store');
    Route::delete('/groups/{group}/invitations/{invitation}', [GroupInvitationController::class, 'destroy'])->name('groups.invitations.destroy');

    Route::delete('/groups/{group}/members/{user}', [GroupMemberController::class, 'destroy'])->name('groups.members.destroy');

    Route::get('/groups/{group}/elements/create', [ElementController::class, 'create'])->name('groups.elements.create');
    Route::post('/groups/{group}/elements', [ElementController::class, 'store'])->name('groups.elements.store');
    Route::get('/groups/{group}/elements/{element}/edit', [ElementController::class, 'edit'])->name('groups.elements.edit');
    Route::patch('/groups/{group}/elements/{element}', [ElementController::class, 'update'])->name('groups.elements.update');
    Route::delete('/groups/{group}/elements/{element}', [ElementController::class, 'destroy'])->name('groups.elements.destroy');

    Route::post('/invitations/{token}/accept', [InvitationAcceptController::class, 'accept'])->name('invitations.accept');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
