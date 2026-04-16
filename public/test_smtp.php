<?php
// Script para probar configuración SMTP
session_start();
require_once '../config/db.php';
require_once '../config/smtp.php';

// Estilos básicos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba SMTP - PLANDET</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 40px 20px; }
        .container { max-width: 600px; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .test-result { margin-top: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">🔧 Prueba de Configuración SMTP</h3>
        </div>
        <div class="card-body">

<?php
// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = trim($_POST['test_email'] ?? '');
    
    if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger">❌ Correo inválido. Por favor ingresa un correo válido.</div>';
    } else {
        // Crear instancia del servicio SMTP
        require_once '../app/services/SmtpMailerService.php';
        $mailer = new SmtpMailerService();
        
        // Datos de prueba
        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial;color:#333;">
    <h2>Prueba de Envío SMTP</h2>
    <p>Este es un mensaje de prueba para verificar que la configuración SMTP funciona correctamente.</p>
    <p><strong>Estado:</strong> ✅ Prueba exitosa</p>
    <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
    <hr>
    <p>Atentamente,<br>PLANDET - Sistema de Reuniones</p>
</body>
</html>
HTML;
        
        $textBody = "Prueba de Envío SMTP\n\n"
                  . "Este es un mensaje de prueba para verificar que la configuración SMTP funciona correctamente.\n\n"
                  . "Estado: ✅ Prueba exitosa\n"
                  . "Fecha: " . date('d/m/Y H:i:s') . "\n\n"
                  . "Atentamente,\nPLANDET - Sistema de Reuniones";
        
        // Intentar enviar
        $result = $mailer->sendEmail(
            $testEmail,
            'Usuario de Prueba',
            '✅ Prueba de Configuración SMTP - PLANDET',
            $htmlBody,
            $textBody
        );
        
        // Mostrar resultado
        if ($result['success'] && $result['status'] === 'sent') {
            echo '<div class="alert alert-success test-result">';
            echo '✅ <strong>¡Éxito!</strong> El correo se envió correctamente a ' . htmlspecialchars($testEmail);
            echo '</div>';
        } elseif ($result['status'] === 'skipped') {
            echo '<div class="alert alert-warning test-result">';
            echo '⚠️ <strong>SMTP deshabilitado:</strong> ' . htmlspecialchars($result['error'] ?? '');
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger test-result">';
            echo '❌ <strong>Error en envío:</strong><br>';
            echo htmlspecialchars($result['error'] ?? 'Error desconocido');
            echo '</div>';
        }
        
        echo '<div class="alert alert-info mt-3"><strong>Respuesta completa:</strong><pre>';
        echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo '</pre></div>';
    }
}
?>

            <h4 class="mt-4">Estado de Configuración</h4>
            
            <?php
            $config = require '../config/smtp.php';
            
            $checks = [
                'SMTP Habilitado' => [
                    'status' => $config['enabled'] ? 'success' : 'error',
                    'value' => $config['enabled'] ? 'Sí ✅' : 'No ❌'
                ],
                'Host SMTP' => [
                    'status' => !empty($config['host']) ? 'success' : 'error',
                    'value' => !empty($config['host']) ? htmlspecialchars($config['host']) : 'No configurado'
                ],
                'Puerto' => [
                    'status' => !empty($config['port']) ? 'success' : 'error',
                    'value' => $config['port'] ?? 'No configurado'
                ],
                'Encriptación' => [
                    'status' => !empty($config['encryption']) ? 'success' : 'error',
                    'value' => $config['encryption'] ?? 'No configurado'
                ],
                'Usuario (Email)' => [
                    'status' => !empty($config['username']) ? 'success' : 'error',
                    'value' => !empty($config['username']) ? htmlspecialchars($config['username']) : 'No configurado'
                ],
                'Contraseña' => [
                    'status' => !empty($config['password']) ? 'success' : 'error',
                    'value' => !empty($config['password']) ? '***' . substr($config['password'], -4) : 'No configurada'
                ],
                'Email Remitente' => [
                    'status' => !empty($config['from_email']) ? 'success' : 'error',
                    'value' => !empty($config['from_email']) ? htmlspecialchars($config['from_email']) : 'No configurado'
                ],
                'Nombre Remitente' => [
                    'status' => !empty($config['from_name']) ? 'success' : 'error',
                    'value' => !empty($config['from_name']) ? htmlspecialchars($config['from_name']) : 'No configurado'
                ]
            ];
            
            foreach ($checks as $label => $check) {
                $icon = $check['status'] === 'success' ? '✅' : '❌';
                echo '<div class="mb-2">';
                echo '<span class="' . $check['status'] . '"><strong>' . $icon . ' ' . $label . ':</strong></span> ';
                echo '<code>' . $check['value'] . '</code>';
                echo '</div>';
            }
            ?>

            <hr class="my-4">

            <h4>Enviar Correo de Prueba</h4>
            <form method="POST" class="mt-3">
                <div class="mb-3">
                    <label for="test_email" class="form-label">Correo de Destino:</label>
                    <input type="email" class="form-control" id="test_email" name="test_email" 
                           placeholder="ejemplo@gmail.com" required>
                    <small class="text-muted">Ingresa tu correo para recibir un mensaje de prueba</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    📧 Enviar Correo de Prueba
                </button>
            </form>

            <div class="alert alert-info mt-4" style="font-size: 0.95rem;">
                <strong>📋 Instrucciones:</strong>
                <ol class="mb-0">
                    <li>Verifica que todos los valores anteriores estén correctamente configurados</li>
                    <li>Si alguno dice "No configurado", edita <code>config/smtp.php</code></li>
                    <li>Ingresa un correo válido y haz clic en "Enviar Correo de Prueba"</li>
                    <li>Si la prueba es exitosa, revisa la carpeta "Recibidos" o "Spam"</li>
                </ol>
            </div>

        </div>
    </div>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">← Volver al Inicio</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
