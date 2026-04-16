<?php
session_start();
require_once '../config/db.php';

// Autoloader simple
spl_autoload_register(function ($class_name) {
    if (file_exists('../app/controllers/' . $class_name . '.php')) {
        require_once '../app/controllers/' . $class_name . '.php';
    } elseif (file_exists('../app/models/' . $class_name . '.php')) {
        require_once '../app/models/' . $class_name . '.php';
    } elseif (file_exists('../app/services/' . $class_name . '.php')) {
        require_once '../app/services/' . $class_name . '.php';
    }
});

$controller_name = isset($_GET['controller']) ? ucfirst($_GET['controller']) . 'Controller' : 'HomeController';
$action_name = isset($_GET['action']) ? $_GET['action'] : 'index';

if (file_exists('../app/controllers/' . $controller_name . '.php')) {
    $controller = new $controller_name();
    if (method_exists($controller, $action_name)) {
        $controller->$action_name();
    } else {
        echo "Accion no encontrada.";
    }
} else {
    echo "Controlador no encontrado.";
}
?>
