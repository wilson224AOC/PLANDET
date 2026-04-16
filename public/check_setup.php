<?php
require_once '../config/db.php';

echo "<h1>Diagnóstico del Sistema</h1>";

// 1. Verificar conexión a BD
echo "<h2>1. Conexión a Base de Datos</h2>";
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos.</p>";
} else {
    echo "<p style='color: red;'>❌ Falló la conexión. Revisa config/db.php</p>";
    die();
}

// 2. Verificar usuario admin
echo "<h2>2. Verificar Usuario Admin</h2>";
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "<p style='color: green;'>✅ Usuario 'admin' encontrado.</p>";
    echo "<p>Hash en base de datos: " . $admin['password'] . "</p>";
    
    // 3. Verificar contraseña
    echo "<h2>3. Verificar Contraseña</h2>";
    $password_to_check = "admin123";
    if (password_verify($password_to_check, $admin['password'])) {
        echo "<p style='color: green;'>✅ La contraseña 'admin123' es correcta para este hash.</p>";
    } else {
        echo "<p style='color: red;'>❌ La contraseña 'admin123' NO coincide con el hash.</p>";
        
        // Generar nuevo hash
        $new_hash = password_hash($password_to_check, PASSWORD_DEFAULT);
        echo "<p>Hash correcto debería ser: $new_hash</p>";
        echo "<p>Intentando actualizar hash automáticamente...</p>";
        
        $update = $conn->prepare("UPDATE admins SET password = :pass WHERE username = 'admin'");
        $update->bindParam(':pass', $new_hash);
        if ($update->execute()) {
            echo "<p style='color: green;'>✅ Hash actualizado. Intenta loguearte ahora.</p>";
        } else {
            echo "<p style='color: red;'>❌ No se pudo actualizar el hash.</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Usuario 'admin' NO encontrado. Ejecuta el script SQL.</p>";
    // Intentar crear usuario
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $insert = $conn->prepare("INSERT INTO admins (username, password) VALUES ('admin', :pass)");
    $insert->bindParam(':pass', $pass);
    if ($insert->execute()) {
         echo "<p style='color: green;'>✅ Usuario 'admin' creado automáticamente.</p>";
    }
}
?>
