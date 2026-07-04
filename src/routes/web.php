<?php

use App\Http\Controllers\AI\DescriptionController;
use App\Http\Controllers\AI\RecommendationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// =====================
// GUEST rute (sve uloge)
// =====================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/calendar', [CalendarController::class, 'download'])->name('events.calendar');

// =====================
// REGISTERED rute (ulogovani korisnici)
// =====================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // Rezervacije
    Route::get('/events/{event}/book', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/events/{event}/book', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::delete('/tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');

    // Recenzije
    Route::post('/events/{event}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // AI preporuke
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
});

// =====================
// ADMIN rute
// =====================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Događaji CRUD
    Route::resource('events', EventController::class)->except(['index', 'show']);

    // Kategorije CRUD
    Route::resource('categories', CategoryController::class);

    // Upravljanje korisnicima
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::patch('/users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');

    // AI generisanje opisa
    Route::get('/events/generate-description', [DescriptionController::class, 'create'])->name('events.generate-description');
    Route::post('/events/generate-description', [DescriptionController::class, 'generate'])->name('events.generate-description.store');
});

require __DIR__.'/auth.php';
