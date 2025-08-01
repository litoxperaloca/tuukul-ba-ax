<?php
// app/controllers/AuthController.php (Actualizado)

class AuthController {
    private $userModel;

    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->userModel = new User($db);
    }

    public function showRegisterForm() {
        $page_title = 'Registro de Usuario';
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/auth/register.php';
        require_once '../app/views/_partials/footer.php';
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validación básica de campos
            if (empty($nombre) || empty($email) || empty($password)) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Todos los campos son obligatorios.'];
                header('Location: /register');
                exit;
            }

            // Verificar si el email ya existe
            if ($this->userModel->findByEmail($email)) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'El correo electrónico ya está registrado.'];
                header('Location: /register');
                exit;
            }

            // Intentar registrar al usuario (por defecto como 'docente' con 1 crédito inicial)
            if ($this->userModel->register($nombre, $email, $password, 'docente')) {
                // Registro exitoso, ahora inicia sesión automáticamente al usuario
                $user = $this->userModel->findByEmail($email); // Recuperar el usuario recién creado para obtener todos los datos

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_credits'] = $user['creditos']; // Asegurarse de guardar los créditos en sesión

                    // Redirigir al dashboard apropiado
                    if ($user['role'] === 'admin') {
                        header('Location: /admin/dashboard');
                    } else {
                        header('Location: /dashboard');
                    }
                    exit;
                } else {
                    // Si por alguna razón no se puede encontrar el usuario recién registrado
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Registro exitoso, pero no se pudo iniciar sesión automáticamente. Por favor, inicia sesión manualmente.'];
                    header('Location: /login?status=reg_success');
                    exit;
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al registrar el usuario. Por favor, inténtalo de nuevo.'];
                header('Location: /register');
                exit;
            }
        }
        // Si no es POST, redirigir al formulario de registro
        header('Location: /register');
        exit;
    }

    public function showLoginForm() {
        $page_title = 'Iniciar Sesión';
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/auth/login.php';
        require_once '../app/views/_partials/footer.php';
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validación básica
            if (empty($email) || empty($password)) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Por favor, ingresa tu correo y contraseña.'];
                header('Location: /login');
                exit;
            }

            $user = $this->userModel->findByEmail($email);

            // Diagnóstico: Verificar si el usuario fue encontrado
            if (!$user) {
                error_log("Intento de login fallido: Usuario con email '{$email}' no encontrado.");
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Correo o contraseña incorrectos.'];
                header('Location: /login?status=login_error');
                exit;
            }

            // Diagnóstico: Verificar si la contraseña coincide
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_credits'] = $user['creditos']; // Asegurarse de guardar los créditos en sesión

                error_log("Login exitoso para el usuario: {$user['email']} con rol: {$user['role']}");

                if ($user['role'] === 'admin') {
                    header('Location: /admin/dashboard');
                } else {
                    header('Location: /dashboard');
                }
                exit;
            } else {
                error_log("Intento de login fallido: Contraseña incorrecta para el usuario '{$email}'.");
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Correo o contraseña incorrectos.'];
                header('Location: /login?status=login_error');
                exit;
            }
        }
        // Si no es POST, redirigir al formulario de login
        header('Location: /login');
        exit;
    }

    public function logout() {
        // Asegurarse de que la sesión esté iniciada antes de destruirla
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        // Redirigir con un mensaje de éxito de logout
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Has cerrado sesión correctamente.'];
        header('Location: /login');
        exit;
    }
}
