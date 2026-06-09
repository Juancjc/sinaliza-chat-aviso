<?php

namespace Database\Seeders;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'tipo_usuario' => 'admin',
                'email_verified_at' => now(),
            ],
        );

        $alunos = collect([
            ['name' => 'Ana Aluna', 'email' => 'ana@example.com'],
            ['name' => 'Bruno Aluno', 'email' => 'bruno@example.com'],
            ['name' => 'Carla Aluna', 'email' => 'carla@example.com'],
        ])->map(fn (array $data) => User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                ...$data,
                'password' => 'password',
                'tipo_usuario' => 'aluno',
                'email_verified_at' => now(),
            ],
        ));

        $grupo = Grupo::query()->updateOrCreate(
            ['nome' => 'Turma de Laravel', 'user_id' => $admin->id],
            ['descricao' => 'Espaço para dúvidas, avisos e troca de conhecimento da turma.'],
        );

        $grupo->participantes()->syncWithoutDetaching(
            $alunos->take(2)->mapWithKeys(fn (User $aluno) => [
                $aluno->id => ['status' => 'ativo'],
            ]),
        );

        if ($grupo->mensagens()->doesntExist()) {
            $grupo->mensagens()->createMany([
                [
                    'user_id' => $admin->id,
                    'mensagem' => 'Bem-vindos ao grupo! Usem este espaço para compartilhar dúvidas.',
                ],
                [
                    'user_id' => $alunos->first()->id,
                    'mensagem' => 'Obrigada! Já estou acompanhando.',
                ],
            ]);
        }
    }
}
