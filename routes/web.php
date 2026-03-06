<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/contacts', [ContactController::class, 'index'])
        ->name('contacts.index');

    Route::get('/contacts/create', [ContactController::class, 'create'])
        ->name('contacts.create');

    Route::post('/contacts', [ContactController::class, 'store'])
        ->name('contacts.store');

    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])
        ->name('contacts.edit');

    Route::put('/contacts/{contact}', [ContactController::class, 'update'])
        ->name('contacts.update');

    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
        ->name('contacts.destroy');

    Route::post('/contacts/{contact}/favorite', [ContactController::class, 'toggleFavorite'])
        ->name('contacts.favorite');
    
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])
    ->name('contacts.show');
});

Route::get('/playground', function () {
    return Inertia::render('Playground');
})->name('playground');


require __DIR__.'/auth.php';
