<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $aviso->titulo }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h1 style="margin-bottom: 4px;">{{ $aviso->titulo }}</h1>
    <p style="margin-top: 0; color: #6b7280;">Grupo: {{ $aviso->grupo->nome }}</p>

    <div style="white-space: pre-line;">{{ $aviso->mensagem }}</div>

    <p style="margin-top: 28px; color: #6b7280;">
        Enviado por {{ $aviso->user->name }}.
    </p>
</body>
</html>
