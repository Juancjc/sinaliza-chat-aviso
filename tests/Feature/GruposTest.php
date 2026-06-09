<?php

use App\Events\AvisoEnviado;
use App\Mail\AvisoGrupoMail;
use App\Models\Grupo;
use App\Models\GrupoConvite;
use App\Models\User;
use App\Notifications\AvisoNaoLido;
use App\Notifications\MensagemNaoLida;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

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

test('mensagem é transmitida pela fila somente para membros e admin criador', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create();
    $participantes = User::factory()->aluno()->count(2)->create();
    $naoParticipante = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach($participantes, ['status' => 'ativo']);

    $this->actingAs($participantes->first())
        ->post(route('grupos.mensagens.store', $grupo), ['mensagem' => 'Mensagem em tempo real'])
        ->assertRedirect();

    Queue::assertPushed(BroadcastEvent::class, function (BroadcastEvent $job) use ($admin, $participantes, $naoParticipante) {
        $event = $job->event;
        $channels = collect($event->broadcastOn())->pluck('name')->sort()->values();
        $expected = $participantes
            ->pluck('id')
            ->push($admin->id)
            ->map(fn (int $id) => "private-users.{$id}")
            ->sort()
            ->values();

        return $event instanceof ShouldBroadcast
            && $channels->all() === $expected->all()
            && ! $channels->contains("private-users.{$naoParticipante->id}");
    });
});

test('mensagem cria notificações não lidas para membros e admin exceto remetente', function () {
    $admin = User::factory()->admin()->create();
    $remetente = User::factory()->aluno()->create();
    $outroAluno = User::factory()->aluno()->create();
    $naoParticipante = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach([$remetente->id, $outroAluno->id], ['status' => 'ativo']);

    $this->actingAs($remetente)
        ->post(route('grupos.mensagens.store', $grupo), ['mensagem' => 'Nova mensagem'])
        ->assertRedirect();

    expect($admin->unreadNotifications()->where('type', MensagemNaoLida::class)->count())->toBe(1)
        ->and($outroAluno->unreadNotifications()->where('type', MensagemNaoLida::class)->count())->toBe(1)
        ->and($remetente->unreadNotifications()->count())->toBe(0)
        ->and($naoParticipante->unreadNotifications()->count())->toBe(0);
});

test('abrir chat marca mensagens daquele grupo como lidas', function () {
    $admin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach($aluno, ['status' => 'ativo']);
    $mensagem = $grupo->mensagens()->create([
        'user_id' => $admin->id,
        'mensagem' => 'Leia ao abrir',
    ]);
    $aluno->notify(new MensagemNaoLida($mensagem));

    $this->actingAs($aluno)
        ->get(route('grupos.chat', $grupo))
        ->assertOk();

    expect($aluno->unreadNotifications()->count())->toBe(0)
        ->and($aluno->readNotifications()->count())->toBe(1);
});

test('somente o dono pode marcar sua notificação como lida', function () {
    $admin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $outroAluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $mensagem = $grupo->mensagens()->create([
        'user_id' => $admin->id,
        'mensagem' => 'Privada',
    ]);
    $aluno->notify(new MensagemNaoLida($mensagem));
    $notificacao = $aluno->unreadNotifications()->first();

    $this->actingAs($outroAluno)
        ->patch(route('notificacoes.read', $notificacao))
        ->assertNotFound();

    $this->actingAs($aluno)
        ->patch(route('notificacoes.read', $notificacao))
        ->assertNoContent();

    expect($notificacao->fresh()->read_at)->not->toBeNull();
});

test('sidebar lista somente grupos autorizados para cada perfil', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupoDoAdmin = $admin->gruposCriados()->create(['nome' => 'Grupo do admin']);
    $outroAdmin->gruposCriados()->create(['nome' => 'Grupo do outro admin']);
    $grupoDoAdmin->participantes()->attach($aluno, ['status' => 'ativo']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('sidebarGroups', 1)
            ->where('sidebarGroups.0.id', $grupoDoAdmin->id)
        );

    $this->actingAs($aluno)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('sidebarGroups', 1)
            ->where('sidebarGroups.0.id', $grupoDoAdmin->id)
        );
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

test('somente o admin criador pode remover aluno do grupo', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach($aluno, ['status' => 'ativo']);

    $this->actingAs($outroAdmin)
        ->delete(route('grupos.participantes.destroy', [$grupo, $aluno]))
        ->assertForbidden();

    $this->actingAs($aluno)
        ->delete(route('grupos.participantes.destroy', [$grupo, $aluno]))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('grupos.participantes.destroy', [$grupo, $aluno]))
        ->assertRedirect();

    expect($grupo->participantes()->whereKey($aluno->id)->exists())->toBeFalse();

    $this->actingAs($aluno)
        ->get(route('grupos.chat', $grupo))
        ->assertForbidden();
});

test('admin criador pode gerar link temporário e um novo link revoga o anterior', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);

    $this->actingAs($aluno)
        ->post(route('grupos.convites.store', $grupo), ['duracao_horas' => 24])
        ->assertForbidden();

    $this->actingAs($outroAdmin)
        ->post(route('grupos.convites.store', $grupo), ['duracao_horas' => 24])
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('grupos.convites.store', $grupo), ['duracao_horas' => 24])
        ->assertRedirect();

    $primeiroConvite = GrupoConvite::first();

    expect($primeiroConvite)
        ->not->toBeNull()
        ->and($primeiroConvite->expires_at->isFuture())->toBeTrue()
        ->and($primeiroConvite->isValid())->toBeTrue();

    $this->actingAs($admin)
        ->post(route('grupos.convites.store', $grupo), ['duracao_horas' => 72])
        ->assertRedirect();

    expect($primeiroConvite->fresh()->revoked_at)->not->toBeNull()
        ->and($grupo->convites()->valid()->count())->toBe(1);
});

test('aluno pode entrar no grupo usando link temporário válido', function () {
    $admin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $convite = $grupo->convites()->create([
        'user_id' => $admin->id,
        'token' => str_repeat('a', 64),
        'expires_at' => now()->addHour(),
    ]);

    $this->get(route('grupos.convites.show', $convite))
        ->assertOk()
        ->assertSessionHas('url.intended', route('grupos.convites.show', $convite));

    $this->post(route('grupos.convites.accept', $convite))
        ->assertRedirect(route('login'));

    $this->post(route('login.store'), [
        'email' => $aluno->email,
        'password' => 'password',
    ])->assertRedirect(route('grupos.convites.show', $convite));

    $this->get(route('grupos.convites.show', $convite))
        ->assertOk();

    $this->post(route('grupos.convites.accept', $convite))
        ->assertRedirect(route('grupos.chat', $grupo));

    expect($grupo->participantes()->whereKey($aluno->id)->exists())->toBeTrue();
});

test('convite expirado não permite entrada e admin não pode aceitar convite', function () {
    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $aluno = User::factory()->aluno()->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $conviteValido = $grupo->convites()->create([
        'user_id' => $admin->id,
        'token' => str_repeat('b', 64),
        'expires_at' => now()->addHour(),
    ]);
    $conviteExpirado = $grupo->convites()->create([
        'user_id' => $admin->id,
        'token' => str_repeat('c', 64),
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($outroAdmin)
        ->post(route('grupos.convites.accept', $conviteValido))
        ->assertForbidden();

    $this->actingAs($aluno)
        ->post(route('grupos.convites.accept', $conviteExpirado))
        ->assertStatus(410);

    expect($grupo->participantes()->whereKey($aluno->id)->exists())->toBeFalse();
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

test('aviso notifica somente alunos e transmite atualização para eles', function () {
    Queue::fake();
    Mail::fake();

    $admin = User::factory()->admin()->create();
    $outroAdmin = User::factory()->admin()->create();
    $alunos = User::factory()->aluno()->count(2)->create();
    $grupo = $admin->gruposCriados()->create(['nome' => 'Grupo']);
    $grupo->participantes()->attach($alunos, ['status' => 'ativo']);

    $this->actingAs($admin)
        ->post(route('grupos.avisos.store', $grupo), [
            'titulo' => 'Aviso importante',
            'mensagem' => 'Leitura obrigatória.',
        ])
        ->assertRedirect(route('dashboard'));

    expect($admin->unreadNotifications()->count())->toBe(0)
        ->and($outroAdmin->unreadNotifications()->count())->toBe(0);

    foreach ($alunos as $aluno) {
        expect($aluno->unreadNotifications()->where('type', AvisoNaoLido::class)->count())->toBe(1);
    }

    Queue::assertPushed(BroadcastEvent::class, function (BroadcastEvent $job) use ($alunos) {
        if (! $job->event instanceof AvisoEnviado) {
            return false;
        }

        $channels = collect($job->event->broadcastOn())->pluck('name')->sort()->values()->all();
        $expected = $alunos->pluck('id')->map(fn (int $id) => "private-users.{$id}")->sort()->values()->all();

        return $channels === $expected;
    });
});
