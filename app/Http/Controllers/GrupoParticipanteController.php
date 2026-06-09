<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipanteRequest;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GrupoParticipanteController extends Controller
{
    public function index(Grupo $grupo): Response
    {
        Gate::authorize('manageParticipants', $grupo);

        return Inertia::render('grupos/Participantes', [
            'grupo' => $grupo->only('id', 'nome', 'descricao'),
            'participantes' => $grupo->participantes()
                ->select('users.id', 'users.name', 'users.email')
                ->orderBy('users.name')
                ->get(),
            'alunosDisponiveis' => User::query()
                ->where('tipo_usuario', 'aluno')
                ->whereDoesntHave('gruposParticipando', fn ($query) => $query->whereKey($grupo->id))
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreParticipanteRequest $request, Grupo $grupo): RedirectResponse
    {
        $grupo->participantes()->attach($request->validated('user_id'), ['status' => 'ativo']);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aluno adicionado ao grupo.']);

        return back();
    }
}
