<?php

use App\Mail\AvisoGrupoMail;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('admin pode criar e gerenciar seu grupo', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('grupos.store'), [
            'nome' => 'Grupo de Teste',
            'descricao' => 'Descrição do grupo',
        ])
        ->assertRedirect();

    $grupo = Grupo::first();

    expect($grupo)
        ->nome->toBe('Grupo de Teste')
        ->user_id->toBe($admin->id);

    $this->actingAs($admin)
        ->put(route('grupos.update', $grupo), [
            'nome' => 'Grupo Atualizado',
            'descricao' => null,
        ])
        ->assertRedirect(route('dashboard'));
});

test('aluno não pode criar grupos', function () {
    $aluno = User::factory()->aluno()->create();

    $this->actingAs($aluno)
        ->post(route('grupos.store'), ['nome' => 'Não permitido'])
        ->assertForbidden();
});

test('admin não pode visualizar grupo criado por outro admin', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);

    $this->actingAs($outroAdmin)
        ->get(route('grupos.chat', $grupo))
        ->assertForbidden();
});

test('aluno acessa e envia mensagem somente no grupo em que participa', function () {
    $admin = User::factory()->admin()->create();
    $participante = User::factory()->aluno()->create();
    $outroAluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach($participante, ['status' => 'ativo']);

    $this->actingAs($participante)
        ->get(route('grupos.chat', $grupo))
        ->assertOk();

    $this->actingAs($participante)
        ->post(route('grupos.mensagens.store', $grupo), ['mensagem' => 'Olá turma!'])
        ->assertRedirect();

    expect($grupo->mensagens()->first()->mensagem)->toBe('Olá turma!');

    $this->actingAs($outroAluno)
        ->get(route('grupos.chat', $grupo))
        ->assertForbidden();

    $this->actingAs($outroAluno)
        ->post(route('grupos.mensagens.store', $grupo), ['mensagem' => 'Invasão'])
        ->assertForbidden();
});

test('somente o admin criador pode adicionar alunos', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);

    $this->actingAs($outroAdmin)
        ->post(route('grupos.participantes.store', $grupo), ['user_id' => $aluno->id])
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('grupos.participantes.store', $grupo), ['user_id' => $aluno->id])
        ->assertRedirect();

    expect($grupo->participantes()->whereKey($aluno->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->post(route('grupos.participantes.store', $grupo), ['user_id' => $aluno->id])
        ->assertSessionHasErrors('user_id');
});

test('admin salva aviso e envia email aos alunos do grupo', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    $alunos = User::factory()->aluno()->count(2)->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach($alunos, ['status' => 'ativo']);

    $this->actingAs($admin)
        ->post(route('grupos.avisos.store', $grupo), [
            'titulo' => 'Aula alterada',
            'mensagem' => 'A aula começará às 19h.',
        ])
        ->assertRedirect(route('dashboard'));

    expect($grupo->avisos()->count())->toBe(1);

    Mail::assertSent(AvisoGrupoMail::class, 2);
});
