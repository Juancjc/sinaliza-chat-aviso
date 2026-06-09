<?php

use App\Http\Controllers\AvisoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\GrupoConviteController;
use App\Http\Controllers\GrupoParticipanteController;
use App\Http\Controllers\NotificacaoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('entrar/{convite}', [GrupoConviteController::class, 'show'])->name('grupos.convites.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('grupos', GrupoController::class)->except(['index', 'show']);

    Route::get('grupos/{grupo}/chat', [ChatController::class, 'show'])->name('grupos.chat');
    Route::post('grupos/{grupo}/mensagens', [ChatController::class, 'store'])->name('grupos.mensagens.store');
    Route::patch('grupos/{grupo}/mensagens/ler', [NotificacaoController::class, 'readGroupMessages'])->name('grupos.mensagens.read');

    Route::get('notificacoes/nao-lidas', [NotificacaoController::class, 'index'])->name('notificacoes.index');
    Route::patch('notificacoes/{notificacao}/ler', [NotificacaoController::class, 'read'])->name('notificacoes.read');

    Route::get('grupos/{grupo}/participantes', [GrupoParticipanteController::class, 'index'])->name('grupos.participantes');
    Route::post('grupos/{grupo}/participantes', [GrupoParticipanteController::class, 'store'])->name('grupos.participantes.store');
    Route::delete('grupos/{grupo}/participantes/{participante}', [GrupoParticipanteController::class, 'destroy'])->name('grupos.participantes.destroy');
    Route::post('grupos/{grupo}/convites', [GrupoConviteController::class, 'store'])->name('grupos.convites.store');
    Route::delete('grupos/{grupo}/convites/{convite}', [GrupoConviteController::class, 'destroy'])->name('grupos.convites.destroy');

    Route::get('grupos/{grupo}/avisos/create', [AvisoController::class, 'create'])->name('grupos.avisos.create');
    Route::post('grupos/{grupo}/avisos', [AvisoController::class, 'store'])->name('grupos.avisos.store');

    Route::post('entrar/{convite}', [GrupoConviteController::class, 'accept'])->name('grupos.convites.accept');
});

require __DIR__.'/settings.php';
