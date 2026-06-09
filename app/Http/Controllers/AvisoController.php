<?php

namespace App\Http\Controllers;

use App\Events\AvisoEnviado;
use App\Http\Requests\StoreAvisoRequest;
use App\Mail\AvisoGrupoMail;
use App\Models\Grupo;
use App\Notifications\AvisoNaoLido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AvisoController extends Controller
{
    public function create(Grupo $grupo): Response
    {
        Gate::authorize('sendAviso', $grupo);

        return Inertia::render('grupos/Aviso', [
            'grupo' => $grupo->loadCount('participantes')->only('id', 'nome', 'descricao', 'participantes_count'),
        ]);
    }

    public function store(StoreAvisoRequest $request, Grupo $grupo): RedirectResponse
    {
        $aviso = $grupo->avisos()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $aviso->load(['grupo', 'user']);
        $alunos = $grupo->participantes()
            ->where('tipo_usuario', 'aluno')
            ->get();

        foreach ($alunos as $aluno) {
            $aluno->notify(new AvisoNaoLido($aviso));
            Mail::to($aluno)->send(new AvisoGrupoMail($aviso));
        }

        AvisoEnviado::dispatch($aviso, $alunos->pluck('id')->all());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Aviso enviado para {$alunos->count()} aluno(s).",
        ]);

        return to_route('dashboard');
    }
}
