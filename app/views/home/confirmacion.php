<?php
// app/views/home/confirmacion.php
$codigo = isset($codigo) ? $codigo : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de reunión cargada</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirm-card {
            max-width: 420px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            padding: 32px 24px;
            text-align: center;
        }
        .confirm-card h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .codigo-box {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 1.1rem;
            font-family: monospace;
        }
        .copy-btn {
            margin-left: 10px;
            background: #e7eafc;
            border: none;
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        .estado {
            display: inline-block;
            background: #fff7d6;
            color: #b48a00;
            border-radius: 8px;
            padding: 4px 18px;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .retorno-btn {
            margin-top: 18px;
            display: inline-block;
            background: #f5f5f5;
            color: #222;
            border: none;
            border-radius: 8px;
            padding: 8px 28px;
            font-size: 1rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .retorno-btn:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="confirm-card">
        <h2>Solicitud de reunión<br>cargada con éxito</h2>
        <div class="codigo-box">
            <span id="codigo-cita"><?php echo htmlspecialchars($codigo); ?></span>
            <button class="copy-btn" onclick="copiarCodigo()">
                <span>📋</span> Copiar código
            </button>
        </div>
        <div class="estado">PENDIENTE</div>
        <br>
        <a href="index.php" class="retorno-btn">&larr; Retorno</a>
    </div>
    <script>
        function copiarCodigo() {
            const codigo = document.getElementById('codigo-cita').innerText;
            navigator.clipboard.writeText(codigo);
        }
    </script>
</body>
</html>
