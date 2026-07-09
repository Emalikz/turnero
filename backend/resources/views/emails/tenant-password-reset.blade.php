<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 40px;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="color: #1e293b; margin-top: 0;">Hola, {{ $userName }}</h2>

        <p style="color: #475569; line-height: 1.6;">
            Recibimos una solicitud para restablecer tu contraseña en Turnero.
        </p>

        <p style="color: #475569; line-height: 1.6;">
            Haz clic en el siguiente enlace para crear una nueva contraseña:
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/t/' . $tenantSlug . '/reset-password?token=' . $token) }}"
               style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
                Restablecer contraseña
            </a>
        </div>

        <p style="color: #94a3b8; font-size: 13px;">
            Este enlace expirará en 60 minutos. Si no solicitaste este cambio, puedes ignorar este mensaje.
        </p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">

        <p style="color: #94a3b8; font-size: 12px; text-align: center;">
            Turnero SaaS &mdash; Gestión de agendas para profesionales
        </p>
    </div>
</body>
</html>
