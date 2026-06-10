<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGrupoRequest;
use App\Http\Requests\UpdateGrupoRequest;
use App\Models\Grupo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GrupoController extends Controller
{
    public function create(): Response
    {
        Gate::authorize('create', Grupo::class);

        return Inertia::render('grupos/Form');
    }

    public function store(StoreGrupoRequest $request): RedirectResponse
    {
        $grupo = $request->user()->gruposCriados()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo criado com sucesso.']);

        return redirect(route('grupos.chat', $grupo, absolute: false));
    }

    public function edit(Grupo $grupo): Response
    {
        Gate::authorize('update', $grupo);

        return Inertia::render('grupos/Form', [
            'grupo' => $grupo->only('id', 'nome', 'descricao'),
        ]);
    }

    public function update(UpdateGrupoRequest $request, Grupo $grupo): RedirectResponse
    {
        $grupo->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo atualizado com sucesso.']);

        return redirect(route('dashboard', absolute: false));
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        Gate::authorize('delete', $grupo);

        $grupo->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo excluído com sucesso.']);

        return redirect(route('dashboard', absolute: false));
    }
}
