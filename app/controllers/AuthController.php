<?php
class AuthController {
    public function login() {
        require_once '../app/views/auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $admin = new Admin();
            $user = $admin->login($username, $password);

            if ($user) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role']     = $user['role'];
                header('Location: index.php?controller=admin&action=index');
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos.";
                require_once '../app/views/auth/login.php';
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
}
?>
