<?php

use App\Http\Controllers\AvisoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\GrupoParticipanteController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('grupos', GrupoController::class)->except(['index', 'show']);

    Route::get('grupos/{grupo}/chat', [ChatController::class, 'show'])->name('grupos.chat');
    Route::get('grupos/{grupo}/mensagens', [ChatController::class, 'messages'])->name('grupos.mensagens.index');
    Route::post('grupos/{grupo}/mensagens', [ChatController::class, 'store'])->name('grupos.mensagens.store');

    Route::get('grupos/{grupo}/participantes', [GrupoParticipanteController::class, 'index'])->name('grupos.participantes');
    Route::post('grupos/{grupo}/participantes', [GrupoParticipanteController::class, 'store'])->name('grupos.participantes.store');

    Route::get('grupos/{grupo}/avisos/create', [AvisoController::class, 'create'])->name('grupos.avisos.create');
    Route::post('grupos/{grupo}/avisos', [AvisoController::class, 'store'])->name('grupos.avisos.store');
});

require __DIR__.'/settings.php';
