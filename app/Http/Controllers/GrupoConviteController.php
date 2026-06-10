<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGrupoConviteRequest;
use App\Models\Grupo;
use App\Models\GrupoConvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GrupoConviteController extends Controller
{
    public function store(StoreGrupoConviteRequest $request, Grupo $grupo): RedirectResponse
    {
        DB::transaction(function () use ($request, $grupo): void {
            $grupo->convites()
                ->valid()
                ->update(['revoked_at' => now()]);

            $grupo->convites()->create([
                'user_id' => $request->user()->id,
                'token' => Str::random(64),
                'expires_at' => now()->addHours($request->integer('duracao_horas')),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Link temporário criado com sucesso.']);

        return back();
    }

    public function destroy(Grupo $grupo, GrupoConvite $convite): RedirectResponse
    {
        Gate::authorize('manageParticipants', $grupo);
        abort_unless($convite->grupo_id === $grupo->id, 404);

        $convite->update(['revoked_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Link temporário revogado.']);

        return back();
    }

    public function show(GrupoConvite $convite): Response
    {
        if (! request()->user()) {
            session()->put('url.intended', route('grupos.convites.show', $convite, absolute: false));
        }

        $convite->load('grupo.admin:id,name');

        return Inertia::render('grupos/Convite', [
            'convite' => [
                'token' => $convite->token,
                'expires_at' => $convite->expires_at,
                'valido' => $convite->isValid(),
                'grupo' => $convite->grupo->only('id', 'nome', 'descricao'),
                'admin' => $convite->grupo->admin->only('id', 'name'),
            ],
        ]);
    }

    public function accept(GrupoConvite $convite): RedirectResponse
    {
        abort_unless($convite->isValid(), 410, 'Este convite expirou ou foi revogado.');
        abort_if(request()->user()->isAdmin(), 403);

        $convite->grupo->participantes()->syncWithoutDetaching([
            request()->user()->id => ['status' => 'ativo'],
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Você entrou no grupo.']);

        return redirect(route('grupos.chat', $convite->grupo, absolute: false));
    }
}
