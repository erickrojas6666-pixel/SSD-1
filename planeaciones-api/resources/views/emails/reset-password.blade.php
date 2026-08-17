<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <!-- Estilos base para mejorar la visualización en clientes de correo -->
    <style>
        /* Reset básico para correos */
        body, table, td, p, a, div, span {
            margin: 0;
            padding: 0;
            border: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        /* Contenedor principal centrado */
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }
        .email-content {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 40px 30px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-container img {
            max-width: 180px;
            height: auto;
            border: 0;
            display: inline-block;
        }
        .btn-primary {
            display: inline-block;
            padding: 14px 32px;
            background-color: #2d3748;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            border: 0;
            text-align: center;
        }
        .btn-primary:hover {
            background-color: #1a202c;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #718096;
            text-align: center;
        }
        .footer a {
            color: #2d3748;
            text-decoration: underline;
        }
        /* Responsive para móviles */
        @media only screen and (max-width: 480px) {
            .email-content {
                padding: 20px 15px;
            }
            .btn-primary {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
            .logo-container img {
                max-width: 140px;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f9fafb; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="email-wrapper" style="max-width:600px; margin:0 auto; padding:20px; background-color:#f9fafb;">
        <div class="email-content" style="background-color:#ffffff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05); padding:40px 30px;">

            <!-- LOGO DE LA INSTITUCIÓN (configurable) -->
            <div class="logo-container" style="text-align:center; margin-bottom:30px;">
                @php
                    $logoPath = config('app.institution_logo', 'images/logo-institucion.png');
                @endphp
                <img src="{{ asset($logoPath) }}" 
                     alt="Logo de {{ config('app.name') }}" 
                     style="max-width:180px; height:auto; border:0; display:inline-block;">
            </div>

            <!-- SALUDO PERSONALIZADO -->
            <h2 style="color:#1a202c; font-size:24px; margin-bottom:16px; font-weight:600;">
                ¡Hola, {{ $user->nombre_completo ?? $user->nombre ?? $user->email }}!
            </h2>

            <!-- MENSAJE PRINCIPAL -->
            <p style="color:#4a5568; font-size:16px; margin-bottom:20px;">
                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>{{ config('app.name') }}</strong>.
                Para continuar, haz clic en el siguiente botón:
            </p>

            <!-- BOTÓN DE ACCIÓN -->
            <p style="text-align:center; margin:30px 0;">
                <a href="{{ $url }}" class="btn-primary" style="display:inline-block; padding:14px 32px; background-color:#2d3748; color:#ffffff !important; text-decoration:none; border-radius:6px; font-weight:600; font-size:16px; border:0; text-align:center;">
                    Restablecer contraseña
                </a>
            </p>

            <!-- NOTA DE SEGURIDAD Y EXPIRACIÓN -->
            <p style="color:#718096; font-size:14px; margin-bottom:12px;">
                ⏱️ Este enlace expirará en <strong>60 minutos</strong> si no se utiliza.
            </p>
            <p style="color:#718096; font-size:14px; margin-bottom:20px;">
                Si no solicitaste este cambio, puedes ignorar este mensaje. Tu contraseña permanecerá segura.
            </p>

            <!-- CIERRE Y FIRMA -->
            <p style="color:#4a5568; font-size:16px; margin-bottom:6px;">
                Gracias por confiar en nosotros.
            </p>
            <p style="color:#4a5568; font-size:16px; margin-bottom:0;">
                Saludos cordiales,<br>
                <strong style="color:#2d3748;">{{ config('app.name') }}</strong>
            </p>

            <!-- PIE DE PÁGINA CON INFORMACIÓN INSTITUCIONAL (opcional) -->
            <div class="footer" style="margin-top:30px; padding-top:20px; border-top:1px solid #e2e8f0; font-size:14px; color:#718096; text-align:center;">
                <p style="margin-bottom:4px;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                </p>
                <p style="margin-bottom:0;">
                    ¿Tienes preguntas? <a href="mailto:{{ config('mail.from.address') }}" style="color:#2d3748; text-decoration:underline;">Contáctanos</a>
                </p>
            </div>

        </div>
    </div>
</body>
</html>