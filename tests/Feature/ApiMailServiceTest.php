<?php

use App\Services\ApiMailService;
use Illuminate\Support\Facades\Http;

test('serviço envia os três campos e credenciais configuradas no env', function () {
    config([
        'services.mail_api.url' => 'https://mail-api.test/send',
        'services.mail_api.key' => 'chave-secreta',
        'services.mail_api.token' => 'token-secreto',
        'services.mail_api.timeout' => 5,
    ]);

    Http::fake([
        'https://mail-api.test/send' => Http::response(['sent' => true]),
    ]);

    app(ApiMailService::class)->send(
        'aluno@example.com',
        'Aviso importante',
        '<h1>Corpo pronto pela Blade</h1>',
    );

    Http::assertSent(function ($request) {
        expect(array_keys($request->data()))->toBe(['to', 'subject', 'body'])
            ->and($request['to'])->toBe('aluno@example.com')
            ->and($request['subject'])->toBe('Aviso importante')
            ->and($request['body'])->toBe('<h1>Corpo pronto pela Blade</h1>')
            ->and($request->header('x-api-key'))->toContain('chave-secreta')
            ->and($request->header('x-api-token'))->toContain('token-secreto');

        return true;
    });
});
