<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $aviso->titulo }}</title>
</head>
<body style="margin: 0; padding: 32px 12px; background-color: #f4f7fb; font-family: Arial, sans-serif; color: #1f2937;">
    <div style="max-width: 620px; margin: 0 auto; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 16px; background-color: #ffffff;">
        <div style="padding: 28px 32px; background-color: #4f46e5; color: #ffffff;">
            <div style="font-size: 13px; font-weight: bold; text-transform: uppercase;">{{ config('app.name') }}</div>
            <h1 style="margin: 10px 0 0; font-size: 26px;">{{ $aviso->titulo }}</h1>
        </div>

        <div style="padding: 32px; font-size: 16px; line-height: 1.7;">
            <p>Olá, {{ $aluno->name }}!</p>
            <p>Você recebeu um novo aviso no grupo <strong>{{ $aviso->grupo->nome }}</strong>:</p>
            <div style="margin: 24px 0; padding: 20px; border-left: 4px solid #4f46e5; background-color: #f8fafc;">
                {!! nl2br(e($aviso->mensagem)) !!}
            </div>
            <p style="color: #64748b;">Enviado por {{ $aviso->user->name }}.</p>
            <a href="{{ config('app.url') }}" style="display: inline-block; margin-top: 16px; padding: 12px 22px; border-radius: 8px; background-color: #4f46e5; color: #ffffff; font-weight: bold; text-decoration: none;">
                Acessar sistema
            </a>
        </div>
    </div>
</body>
</html>
