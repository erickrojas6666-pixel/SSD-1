<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <!-- Espacio para el icono de tu institución -->
        <img src="{{ asset('images/logo-institucion.png') }}" alt="Logo Institución" style="max-width: 150px;">
    </div>

    <h2>¡Hola, {{ $user->nombre_completo ?? $user->email }}!</h2>

    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 24px; background-color: #2d3748; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
            Restablecer contraseña
        </a>
    </p>

    <p>Si no realizaste esta solicitud, ignora este correo.</p>

    <p>Gracias,<br>{{ config('app.name') }}</p>
</body>
</html>