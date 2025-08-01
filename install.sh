#!/bin/bash

# ==============================================================================
# Script de Instalación Completo para Planeación Educativa IA
# ==============================================================================
#
# Este script crea la estructura completa de directorios y todos los archivos
# de la aplicación con su contenido final y listo para producción.
#
# USO:
# 1. Guarda este archivo como `install.sh`.
# 2. Dale permisos de ejecución: chmod +x install.sh
# 3. Ejecútalo en el directorio donde quieras instalar la aplicación: ./install.sh
#

# --- Funciones de Utilidad ---
echo_color() {
    local color_code=$1
    shift
    echo -e "\033[${color_code}m$@\033[0m"
}

# --- Inicio del Script ---
clear
echo_color "1;34" "======================================================"
echo_color "1;34" "==   Instalador de Planeación Educativa con IA    =="
echo_color "1;34" "======================================================"
echo ""
echo_color "1;33" "Este script creará la aplicación completa en el directorio actual."
read -p "¿Estás listo para continuar? (s/n): " confirm
if [[ "$confirm" != "s" && "$confirm" != "S" ]]; then
    echo_color "1;31" "Instalación cancelada."
    exit 1
fi

# --- Paso 1: Recopilar Información de Configuración ---
echo ""
echo_color "1;36" "--- Configuración de la Base de Datos ---"
read -p "Host de la Base de Datos (ej. localhost): " DB_HOST
read -p "Nombre de la Base de Datos (ej. planeacion_db): " DB_NAME
read -p "Usuario de la Base de Datos (ej. planeacion_user): " DB_USER
read -s -p "Contraseña de la Base de Datos: " DB_PASS
echo ""

echo ""
echo_color "1;36" "--- Configuración de la API ---"
read -p "Pega tu clave de API de OpenAI: " OPENAI_API_KEY

echo ""
echo_color "1;36" "--- Configuración de la URL ---"
read -p "URL completa de tu aplicación (ej. https://planeacioneducativa.ironplatform.com.uy): " APP_URL

# --- Paso 2: Crear Estructura de Directorios ---
echo ""
echo_color "1;32" "-> Creando estructura de directorios..."
mkdir -p app/{controllers,models,views/{auth,dashboard,admin,_partials}} config public/{css,js} scripts
echo_color "1;32" "   Directorios creados con éxito."

# --- Paso 3: Crear Archivos con Contenido ---
echo_color "1;32" "-> Creando archivos de la aplicación..."

# --- Archivos de Configuración ---
echo_color "1;32" "   - Creando config/config.php"
cat << EOF > config/config.php
<?php
// config/config.php
define('DB_HOST', '${DB_HOST}');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('DB_NAME', '${DB_NAME}');
define('OPENAI_API_KEY', '${OPENAI_API_KEY}');
define('APP_URL', '${APP_URL}');
?>
EOF

echo_color "1;32" "   - Creando config/database.sql"
cat << 'EOF' > config/database.sql
CREATE DATABASE IF NOT EXISTS `planeacion_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `planeacion_db`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('docente','admin') NOT NULL DEFAULT 'docente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE `planeaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `prompt_data` JSON NOT NULL,
  `respuesta_ia` LONGTEXT DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `planeaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
EOF

# --- Directorio Public ---
echo_color "1;32" "   - Creando public/.htaccess"
cat << 'EOF' > public/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]
</IfModule>
EOF

echo_color "1;32" "   - Creando public/index.php"
cat << 'EOF' > public/index.php
<?php
session_start();
require_once '../config/config.php';
spl_autoload_register(function ($class_name) {
    $paths = [
        '../app/controllers/' . $class_name . '.php',
        '../app/models/' . $class_name . '.php'
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
$route = $_GET['route'] ?? 'home';
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
if (strpos($route, 'admin/') === 0 && !$is_admin) {
    http_response_code(403);
    die('Acceso Prohibido');
}
$protected_routes = ['dashboard', 'dashboard/create', 'api/generate'];
if (in_array($route, $protected_routes) && !$is_logged_in) {
    if (strpos($route, 'api/') === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    } else {
        header('Location: /login');
    }
    exit;
}
switch ($route) {
    case 'home':
        (new HomeController())->index();
        break;
    case 'login':
        (new AuthController())->showLoginForm();
        break;
    case 'process-login':
        (new AuthController())->processLogin();
        break;
    case 'register':
        (new AuthController())->showRegisterForm();
        break;
    case 'process-register':
        (new AuthController())->processRegister();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'dashboard/create':
        (new DashboardController())->showCreateForm();
        break;
    case 'api/generate':
        (new ApiController())->generatePlaneacion();
        break;
    case 'admin/dashboard':
        (new AdminController())->index();
        break;
    default:
        http_response_code(404);
        echo "<h1>404 - Página No Encontrada</h1>";
        break;
}
EOF

echo_color "1;32" "   - Creando public/css/style.css"
touch public/css/style.css

echo_color "1;32" "   - Creando public/js/main.js"
cat << 'EOF' > public/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    const planeacionForm = document.getElementById('planeacion-form');
    if (planeacionForm) {
        const generateBtn = document.getElementById('generate-btn');
        const loader = document.getElementById('loader');
        const responseContainer = document.getElementById('ai-response-container');
        planeacionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generando...';
            loader.classList.remove('hidden');
            responseContainer.innerHTML = '';
            const formData = new FormData(planeacionForm);
            try {
                const response = await fetch('/api/generate', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) {
                    const errorResult = await response.json().catch(() => ({ message: `Error del servidor: ${response.status}` }));
                    throw new Error(errorResult.message);
                }
                const result = await response.json();
                if (result.success) {
                    window.location.href = '/dashboard?status=success';
                } else {
                    throw new Error(result.message || 'Ocurrió un error desconocido.');
                }
            } catch (error) {
                responseContainer.innerHTML = `<p class="text-red-600 font-semibold">Error al generar: ${error.message}</p>`;
                loader.classList.add('hidden');
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> Generar Planeación';
            }
        });
    }
});
EOF

# --- Modelos ---
echo_color "1;32" "   - Creando app/models/Database.php"
cat << 'EOF' > app/models/Database.php
<?php
class Database {
    private static $instance = null;
    private $conn;
    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public function getConnection() {
        return $this->conn;
    }
}
EOF

echo_color "1;32" "   - Creando app/models/User.php"
cat << 'EOF' > app/models/User.php
<?php
class User {
    private $conn;
    private $table = 'usuarios';
    public function __construct($db) { $this->conn = $db; }
    public function register($nombre, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO " . $this->table . " (nombre, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $nombre, $email, $hashed_password);
        return $stmt->execute();
    }
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function getAll() {
        $query = "SELECT id, nombre, email, role, fecha_registro FROM " . $this->table . " ORDER BY fecha_registro DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
EOF

echo_color "1;32" "   - Creando app/models/Planeacion.php"
cat << 'EOF' > app/models/Planeacion.php
<?php
class Planeacion {
    private $conn;
    private $table = 'planeaciones';
    public function __construct($db) { $this->conn = $db; }
    public function create($id_usuario, $prompt_data, $respuesta_ia) {
        $query = "INSERT INTO " . $this->table . " (id_usuario, prompt_data, respuesta_ia) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $prompt_json = json_encode($prompt_data);
        $stmt->bind_param("iss", $id_usuario, $prompt_json, $respuesta_ia);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    public function getByUserId($id_usuario) {
        $query = "SELECT id, prompt_data, fecha_creacion FROM " . $this->table . " WHERE id_usuario = ? ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) { return []; }
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
EOF

# --- Controladores ---
echo_color "1;32" "   - Creando app/controllers/HomeController.php"
cat << 'EOF' > app/controllers/HomeController.php
<?php
class HomeController {
    public function index() {
        $page_title = 'Bienvenido a Planeación Educativa IA';
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/home/index.php';
        require_once '../app/views/_partials/footer.php';
    }
}
EOF

echo_color "1;32" "   - Creando app/controllers/AuthController.php"
cat << 'EOF' > app/controllers/AuthController.php
<?php
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
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            if (!empty($nombre) && !empty($email) && !empty($password) && !$this->userModel->findByEmail($email)) {
                if ($this->userModel->register($nombre, $email, $password)) {
                    header('Location: /login?status=reg_success');
                    exit;
                }
            }
        }
        header('Location: /register?status=reg_error');
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
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                if ($user['role'] === 'admin') {
                    header('Location: /admin/dashboard');
                } else {
                    header('Location: /dashboard');
                }
                exit;
            }
        }
        header('Location: /login?status=login_error');
        exit;
    }
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
EOF

echo_color "1;32" "   - Creando app/controllers/DashboardController.php"
cat << 'EOF' > app/controllers/DashboardController.php
<?php
class DashboardController {
    private $planeacionModel;
    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->planeacionModel = new Planeacion($db);
    }
    public function index() {
        $page_title = 'Mis Planeaciones';
        $planeaciones = $this->planeacionModel->getByUserId($_SESSION['user_id']);
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/index.php';
        require_once '../app/views/_partials/footer.php';
    }
    public function showCreateForm() {
        $page_title = 'Crear Nueva Planeación';
        $niveles_grados = [
            "Preescolar - 1°" => "Fase 2", "Preescolar - 2°" => "Fase 2", "Preescolar - 3°" => "Fase 2",
            "Primaria - 1°" => "Fase 3", "Primaria - 2°" => "Fase 3", "Primaria - 3°" => "Fase 4", "Primaria - 4°" => "Fase 4",
            "Primaria - 5°" => "Fase 5", "Primaria - 6°" => "Fase 5", "Secundaria - 1°" => "Fase 6", "Secundaria - 2°" => "Fase 6", "Secundaria - 3°" => "Fase 6"
        ];
        $contenidos_por_grado = [
            "Preescolar - 1°" => ["Capacidades y habilidades motrices" => "Explora movimientos de locomoción, manipulación o estabilidad para enriquecer sus posibilidades.", "Posibilidades expresivas y motrices" => "Explora sus posibilidades expresivas en situaciones lúdicas que impliquen el uso de los sentidos para favorecer capacidades perceptivo-motrices.", "Estilos de vida activos y saludables" => "Participa en juegos y dinámicas que le permitan mantenerse físicamente activo en distintos momentos.", "Pensamiento lúdico, divergente y creativo" => "Descubre distintas soluciones para resolver situaciones de juego e interacción.", "Interacción motriz" => "Experimenta distintas maneras de jugar e interactuar para identificar acuerdos que favorecen la convivencia."],
            "Preescolar - 2°" => ["Capacidades y habilidades motrices" => "Combina movimientos de locomoción, manipulación y estabilidad en juegos para dar respuesta a las situaciones que se presentan en cada una.", "Posibilidades expresivas y motrices" => "Emplea su cuerpo al comunicarse y representar ideas, situaciones y emociones para favorecer sus capacidades perceptivo-motrices.", "Estilos de vida activos y saludables" => "Propone juegos y actividades que le ayudan a mantenerse físicamente activo, de acuerdo con sus posibilidades.", "Pensamiento lúdico, divergente y creativo" => "Propone diferentes maneras de solucionar una situación o problema, para favorecer su creatividad.", "Interacción motriz" => "Establece normas de interacción ante distintas situaciones para actuar en favor de una convivencia sana."],
            "Preescolar - 3°" => ["Capacidades y habilidades motrices" => "Adapta sus movimientos de locomoción, manipulación y estabilidad ante las situaciones que se le presentan para favorecer la precisión y control de sus movimientos.", "Posibilidades expresivas y motrices" => "Elabora propuestas de códigos de comunicación por medio del cuerpo para otorgarle una intención a sus movimientos al jugar e interactuar con los demás.", "Estilos de vida activos y saludables" => "Socializa actividades físicas que practica de manera cotidiana, con la intención de valorar su incidencia en el bienestar y cuidado de la salud.", "Pensamiento lúdico, divergente y estratégico" => "Toma decisiones estratégicas a partir de las características de las situaciones de juego y cotidianas, con el fin de solucionarlas asertivamente.", "Interacción motriz" => "Establece acuerdos ante situaciones de juego y cotidianas a partir de la interacción, para valorar su aplicación y los resultados alcanzados."],
            "Primaria - 1°" => ["Capacidades y habilidades motrices" => "Utiliza patrones básicos de movimiento ante situaciones que implican explorar los diferentes espacios, el tiempo y los objetos, para favorecer el conocimiento de sí.", "Posibilidades cognitivas, expresivas, motrices, creativas y de relación" => "Explora acciones motrices y expresivas en juegos y situaciones cotidianas que implican equilibrio, orientación espacial-temporal y coordinación motriz, para mejorar el conocimiento de sí.", "Estilos de vida activos y saludables" => "Participa en diferentes juegos para explorar alternativas que le permitan mantener una vida activa y saludable.", "Pensamiento lúdico, divergente y estratégico" => "Busca distintas soluciones ante una misma situación de juego o cotidiana, con la intención de poner en práctica la creatividad.", "Interacción motriz" => "Colabora en la definición de normas básicas de convivencia para reconocer su influencia en la interacción presente en juegos y situaciones cotidianas."],
            "Primaria - 2°" => ["Capacidades y habilidades motrices" => "Combina diversos patrones básicos de movimiento para actuar con base en las características de cada juego o situación.", "Posibilidades cognitivas, expresivas, motrices, creativas y de relación" => "Experimenta acciones que implican comunicación y expresión por medio del cuerpo, para asignar un carácter personal a sus movimientos y mejorar la interacción.", "Estilos de vida activos y saludables" => "Reconoce propuestas lúdicas o expresivas que fomentan su disfrute y práctica cotidiana para favorecer una vida activa y saludable.", "Pensamiento lúdico, divergente y estratégico" => "Propone soluciones ante retos y conflictos que se presentan en juegos y actividades, para promover la participación, el respeto y la colaboración.", "Interacción motriz" => "Reflexiona acerca de las normas básicas de convivencia en el juego y las actividades cotidianas, con el propósito de asumir actitudes que fortalezcan la interacción."],
            "Primaria - 3°" => ["Capacidades y habilidades motrices" => "Adapta sus movimientos, de acuerdo con los elementos básicos de los juegos para responder a las condiciones que se presentan.", "Posibilidades cognitivas, expresivas, motrices, creativas y de relación" => "Elabora propuestas de códigos de comunicación por medio del cuerpo para otorgarle una intención a sus movimientos al jugar e interactuar con los demás.", "Estilos de vida activos y saludables" => "Socializa actividades físicas que practica de manera cotidiana, con la intención de valorar su incidencia en el bienestar y cuidado de la salud.", "Pensamiento lúdico, divergente y estratégico" => "Toma decisiones estratégicas a partir de las características de las situaciones de juego y cotidianas, con el fin de solucionarlas asertivamente.", "Interacción motriz" => "Establece acuerdos ante situaciones de juego y cotidianas a partir de la interacción, para valorar su aplicación y los resultados alcanzados."],
            "Primaria - 4°" => ["Capacidades y habilidades motrices" => "Pone en práctica sus habilidades motrices en situaciones lúdicas, individuales y colectivas, para valorar la diversidad de posibilidades que contribuyen a mejorar su actuación.", "Posibilidades cognitivas, expresivas, motrices, creativas y de relación" => "Reconoce sus capacidades y habilidades motrices al representar con el cuerpo situaciones e historias, a fin de favorecer la construcción de la imagen corporal.", "Estilos de vida activos y saludables" => "Organiza juegos y otras actividades físicas para analizar avances y logros personales o grupales en favor de asumir una vida saludable.", "Pensamiento lúdico, divergente y estratégico" => "Diseña estrategias para atender situaciones o resolver problemas y conflictos que se presentan en el juego y en actividades cotidianas.", "Interacción motriz" => "Experimenta situaciones caracterizadas por la cooperación y oposición, con el fin de reconocer sus implicaciones en la interacción y el logro de metas."],
            "Primaria - 5°" => ["Capacidades, habilidades y destrezas motrices" => "Reconoce posibilidades y límites al participar en situaciones de juego e iniciación deportiva, individuales y colectivas, para valorar su desempeño y determinar posibles mejoras.", "Potencialidades cognitivas, expresivas, motrices, creativas y de relación" => "Integra sus capacidades y habilidades en situaciones lúdicas y expresivas (individuales y colectivas), para lograr mayor seguridad y confianza.", "Estilos de vida activos y saludables" => "Plantea alternativas de actividades físicas que puede practicar dentro y fuera de la escuela, con la intención de desarrollar un estilo de vida activo.", "Pensamiento lúdico, estratégico y creativo" => "Planifica e implementa estrategias ante situaciones de juego y cotidianas, para contar con opciones que incrementen la efectividad de su actuación.", "Interacción motriz" => "Promueve ambientes de participación en situaciones de juego, iniciación deportiva y cotidianas, para valorar posibles interacciones en favor de una sana convivencia."],
            "Primaria - 6°" => ["Capacidades, habilidades y destrezas motrices" => "Aplica sus capacidades, habilidades y destrezas motrices al organizar y participar en situaciones de juego e iniciación deportiva, para favorecer su disponibilidad corporal.", "Potencialidades cognitivas, expresivas, motrices, creativas y de relación" => "Diseña propuestas de actividades lúdicas y expresivas a partir de sus intereses, capacidades y habilidades, para fortalecer su imagen corporal.", "Estilos de vida activos y saludables" => "Evalúa los factores que limitan la práctica constante de actividades físicas, para implementar opciones que permitan superarlos a lo largo de la vida.", "Pensamiento lúdico, estratégico y creativo" => "Emplea el pensamiento estratégico y divergente ante situaciones de juego o cotidianas, para valorar la actuación, individual y colectiva, y adaptarla de acuerdo con el contexto.", "Interacción motriz" => "Organiza e implementa situaciones de juego e iniciación deportiva, para favorecer la convivencia en la escuela y la comunidad."],
            "Secundaria - 1°" => ["Capacidades, habilidades y destrezas motrices" => "Explora las capacidades, habilidades y destrezas motrices para enriquecer y ampliar el potencial propio y de las demás personas.", "Potencialidades cognitivas, expresivas, motrices, creativas y de relación" => "Pone en práctica los elementos de la condición física en actividades motrices y recreativas para reconocerlas como alternativas que fomentan el bienestar individual y colectivo.", "Estilos de vida activos y saludables" => "Implementa acciones que le permiten mantenerse físicamente activo en diferentes momentos del día, para favorecer la práctica de estilos de vida saludables.", "Pensamiento lúdico, divergente y creativo" => "Toma decisiones individuales y colectivas en situaciones de juego (defensivas u ofensivas), con el propósito de valorar su efectividad.", "Interacción motriz" => "Pone a prueba la interacción motriz en situaciones de juego, iniciación deportiva y deporte educativo, con el fin de alcanzar metas comunes y obtener satisfacción al colaborar con las demás personas."],
            "Secundaria - 2°" => ["Capacidades, habilidades y destrezas motrices" => "Integra sus capacidades, habilidades y destrezas motrices para poner a prueba el potencial individual y de conjunto.", "Potencialidades cognitivas, expresivas, motrices, creativas y de relación" => "Analiza el incremento de su condición física al participar en actividades recreativas, de iniciación deportiva y deporte educativo, para reflexionar acerca de su relación con el bienestar.", "Estilos de vida activos y saludables" => "Reflexiona acerca de los factores que inciden en la práctica sistemática de actividad física para proponer acciones que contribuyan a modificarlos o eliminarlos.", "Pensamiento lúdico, divergente y creativo" => "Valora las estrategias de juego que utiliza, ante distintas condiciones que se presentan, para reestructurarlas e incrementar su efectividad.", "Interacción motriz" => "Toma decisiones a favor de la participación colectiva en situaciones de iniciación deportiva y deporte educativo, para promover ambientes de aprendizaje y actitudes asertivas."],
            "Secundaria - 3°" => ["Capacidades, habilidades y destrezas motrices" => "Valora las capacidades, habilidades y destrezas propias y de las demás personas, para mostrar mayor disponibilidad corporal y autonomía motriz.", "Potencialidades cognitivas, expresivas, motrices, creativas y de relación" => "Diseña, organiza y participa en actividades recreativas, de iniciación deportiva y deporte educativo, con la intención de fomentar el bienestar personal y social.", "Estilos de vida activos y saludables" => "Diseña alternativas que fomenten la práctica de estilos de vida activos y saludables a partir del análisis de comportamientos que ponen en riesgo la salud, para hacer frente a problemas asociados con el sedentarismo.", "Pensamiento lúdico, divergente y creativo" => "Emplea el pensamiento estratégico para favorecer la colaboración y creatividad en la resolución de situaciones individuales y colectivas.", "Interacción motriz" => "Promueve relaciones asertivas con las demás personas en situaciones de juego, iniciación deportiva y deporte educativo, para fortalecer su autoestima y fomentar el juego limpio y la confrontación lúdica."]
        ];
        $ejes_articuladores = ["Inclusión", "Pensamiento crítico", "Interculturalidad crítica", "Igualdad de género", "Vida saludable", "Apropiación de las culturas a través de la lectura y la escritura", "Artes y experiencias estéticas"];
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/create.php';
        require_once '../app/views/_partials/footer.php';
    }
}
EOF

echo_color "1;32" "   - Creando app/controllers/AdminController.php"
cat << 'EOF' > app/controllers/AdminController.php
<?php
class AdminController {
    private $userModel;
    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->userModel = new User($db);
    }
    public function index() {
        $page_title = 'Panel de Administración';
        $users = $this->userModel->getAll();
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/index.php';
        require_once '../app/views/_partials/footer.php';
    }
}
EOF

echo_color "1;32" "   - Creando app/controllers/ApiController.php"
cat << 'EOF' > app/controllers/ApiController.php
<?php
class ApiController {
    public function generatePlaneacion() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        $formData = $_POST;
        $contenido_pda_parts = explode('||', $formData['contenido_pda'] ?? '||');
        $contenido = trim($contenido_pda_parts[0]);
        $pda = trim($contenido_pda_parts[1] ?? '');
        $ejes = isset($formData['ejes_articuladores']) ? implode(', ', $formData['ejes_articuladores']) : 'No seleccionados';
        $duracion_desarrollo = (int)($formData['duracion_sesion'] ?? 50) - 15;
        $prompt = "Actúa como un experto en planeación didáctica de Educación Física en México, alineado al marco curricular vigente de la Nueva Escuela Mexicana.
Tu tarea es diseñar una planeación didáctica completa y detallada a partir de la siguiente información proporcionada por el docente:

**DATOS PROPORCIONADOS:**
* **Escuela y CCT:** " . htmlspecialchars($formData['escuela'] ?? 'No especificada') . " / " . htmlspecialchars($formData['cct'] ?? 'No especificado') . "
* **Nivel Educativo y Grado:** " . htmlspecialchars($formData['nivel_grado'] ?? 'No especificado') . " (Fase correspondiente: " . htmlspecialchars($formData['fase'] ?? 'No especificada') . ")
* **Contenido Curricular Seleccionado:** " . htmlspecialchars($contenido) . "
* **Proceso de Desarrollo de Aprendizaje (PDA):** " . htmlspecialchars($pda) . "
* **Número de Sesiones:** " . htmlspecialchars($formData['num_sesiones'] ?? 1) . "
* **Materiales Disponibles:** " . htmlspecialchars($formData['materiales'] ?? 'No especificados') . "
* **Ejes Articuladores Seleccionados:** " . htmlspecialchars($ejes) . "
* **Alumnos con Necesidades Educativas Especiales (NEE):** " . (empty($formData['alumnos_nee']) ? 'Ninguno' : htmlspecialchars($formData['alumnos_nee'])) . "
* **Duración por Sesión:** " . htmlspecialchars($formData['duracion_sesion'] ?? 50) . " minutos

**INSTRUCCIONES PARA LA GENERACIÓN:**

**1. ESTRUCTURA POR SESIÓN (Repetir para cada una de las " . htmlspecialchars($formData['num_sesiones'] ?? 1) . " sesiones):**
Cada sesión debe contener:
* **Propósito específico:** Redactado con claridad y alineado al PDA.
* **Materiales:** Lista detallada de los materiales mencionados que se usarán en esta sesión.
* **Inicio (5 min):** Una actividad de activación lúdica, musical o sensorial.
* **Desarrollo (" . $duracion_desarrollo . " min):** Secuencia de actividades que incluya:
    * Actividad de exploración corporal.
    * Actividad expresiva o reto motor.
    * Juego cooperativo o actividad integradora.
* **Cierre (10 min):** Actividad de vuelta a la calma y reflexión (motriz o emocional) con preguntas detonadoras.
* **Evaluación por sesión:** Un indicador observable, específico y medible.
* **Adecuaciones Específicas:** Si se proporcionó información de NEE, describe aquí las adaptaciones concretas para esta sesión.

**2. CRITERIOS DIDÁCTICOS CLAVE (OBLIGATORIOS):**
* **Coherencia Curricular:** Las actividades deben estar estrictamente adaptadas al grado, fase y PDA proporcionados.
* **Variedad y Progresión:** No repitas actividades entre sesiones. Asegura una variedad motriz, expresiva, rítmica y cooperativa. Las actividades deben ser lúdicas y desafiantes.
* **Integración de Ejes:** Integra visiblemente los ejes articuladores seleccionados en las actividades y reflexiones.
* **Inclusión:** Adapta el contexto para favorecer la participación plena de todos, especialmente si hay alumnos con NEE.

**3. EVALUACIÓN GRUPAL FINAL:**
Al final de todas las sesiones, genera un instrumento de evaluación grupal (elige el más adecuado: lista de cotejo o rúbrica) en formato de tabla Markdown. Debe incluir indicadores claros y observables sobre conductas motrices, actitudes de convivencia y participación.

**REGLA FINAL:** Utiliza exclusivamente los contenidos y procesos oficiales. No inventes PDA ni contenidos que no correspondan al marco curricular.";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "model" => "gpt-4o",
            "messages" => [
                ["role" => "system", "content" => "Eres un experto planificador didáctico de Educación Física en México, preciso y creativo."],
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.7,
            "max_tokens" => 3000
        ]));
        $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . OPENAI_API_KEY];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $api_response = curl_exec($ch);
        if (curl_errno($ch)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al contactar la API de OpenAI: ' . curl_error($ch)]);
            curl_close($ch);
            exit;
        }
        curl_close($ch);
        $api_data = json_decode($api_response, true);
        if (isset($api_data['error'])) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de la API de OpenAI: ' . $api_data['error']['message']]);
            exit;
        }
        $ai_text_response = $api_data['choices'][0]['message']['content'] ?? 'No se pudo obtener una respuesta de la IA.';
        try {
            $db = Database::getInstance()->getConnection();
            $planeacionModel = new Planeacion($db);
            $planeacionModel->create($_SESSION['user_id'], $formData, $ai_text_response);
        } catch (Exception $e) {
            error_log("Error al guardar en la base de datos: " . $e->getMessage());
        }
        echo json_encode(['success' => true, 'data' => $ai_text_response]);
    }
}
EOF

# --- Vistas ---
echo_color "1;32" "   - Creando app/views/_partials/header.php"
cat << 'EOF' > app/views/_partials/header.php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Planeación Educativa IA' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .font-roboto { font-family: 'Roboto', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-shadow { box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold text-indigo-600">
                <i class="fas fa-brain mr-2"></i>Planea<span class="text-purple-600">IA</span>
            </a>
            <nav class="space-x-4 flex items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/admin/dashboard" class="text-red-500 hover:text-red-700 font-semibold">
                            <i class="fas fa-user-shield mr-1"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="/dashboard" class="text-gray-600 hover:text-indigo-600 font-medium">Dashboard</a>
                    <a href="/logout" class="bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors duration-300 text-sm font-semibold">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/login" class="text-gray-600 hover:text-indigo-600 font-medium">Iniciar Sesión</a>
                    <a href="/register" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors duration-300 text-sm font-semibold">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>
EOF

echo_color "1;32" "   - Creando app/views/_partials/footer.php"
cat << 'EOF' > app/views/_partials/footer.php
    </main>
    <footer class="bg-gray-800 text-white mt-16">
        <div class="container mx-auto py-8 px-6 text-center">
            <p class="font-roboto">&copy; <?= date('Y') ?> Planeación Educativa IA. Una herramienta para potenciar la educación.</p>
        </div>
    </footer>
    <script src="/js/main.js"></script>
</body>
</html>
EOF

echo_color "1;32" "   - Creando app/views/home/index.php"
cat << 'EOF' > app/views/home/index.php
<div class="bg-gray-100">
    <div class="container mx-auto px-6 py-16 md:py-24 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
            ¡Bienvenidos, docentes!
        </h1>
        <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto font-roboto mb-10">
            Esta es tu nueva herramienta para crear planeaciones didácticas de forma rápida e inteligente. Simplifica tu trabajo y potencia tu enseñanza con el poder de la IA.
        </p>
        <div class="flex flex-col md:flex-row justify-center items-center gap-6">
            <div class="bg-white p-8 rounded-xl card-shadow w-full md:w-1/3 text-center">
                <i class="fas fa-user-check text-4xl text-indigo-500 mb-4"></i>
                <h2 class="text-2xl font-bold mb-2">¿Ya tienes una cuenta?</h2>
                <p class="text-gray-600 mb-6 font-roboto h-16">Ingresa a tu panel de control para continuar creando y gestionar tus planeaciones.</p>
                <a href="/login" class="w-full inline-block bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-lg">
                    Iniciar Sesión
                </a>
            </div>
            <div class="bg-white p-8 rounded-xl card-shadow w-full md:w-1/3 text-center">
                <i class="fas fa-user-plus text-4xl text-purple-500 mb-4"></i>
                <h2 class="text-2xl font-bold mb-2">¿Eres nuevo aquí?</h2>
                <p class="text-gray-600 mb-6 font-roboto h-16">Crea tu cuenta gratis en segundos y empieza a innovar en el aula.</p>
                <a href="/register" class="w-full inline-block bg-purple-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors duration-300 shadow-lg">
                    Crear una Cuenta
                </a>
            </div>
        </div>
    </div>
</div>
EOF

echo_color "1;32" "   - Creando app/views/auth/login.php"
cat << 'EOF' > app/views/auth/login.php
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl card-shadow">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Inicia sesión
            </h2>
             <p class="mt-2 text-center text-sm text-gray-600 font-roboto">
                Nos alegra verte de nuevo por aquí.
            </p>
        </div>
        <form class="mt-8 space-y-6" action="/process-login" method="POST">
             <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Correo electrónico">
                </div>
                <div>
                    <label for="password" class="sr-only">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Contraseña">
                </div>
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Entrar</button>
            </div>
        </form>
        <p class="mt-2 text-center text-sm text-gray-600 font-roboto">¿No tienes una cuenta? <a href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">Regístrate gratis</a></p>
    </div>
</div>
EOF

echo_color "1;32" "   - Creando app/views/auth/register.php"
cat << 'EOF' > app/views/auth/register.php
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl card-shadow">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Crea tu cuenta de docente
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 font-roboto">
                Y empieza a transformar tu forma de planear y enseñar.
            </p>
        </div>
        <form class="mt-8 space-y-6" action="/process-register" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="nombre" class="sr-only">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Nombre completo">
                </div>
                <div>
                    <label for="email" class="sr-only">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Correo electrónico">
                </div>
                <div>
                    <label for="password" class="sr-only">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Contraseña">
                </div>
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Registrarse</button>
            </div>
        </form>
         <p class="mt-2 text-center text-sm text-gray-600 font-roboto">¿Ya tienes una cuenta? <a href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">Inicia sesión aquí</a></p>
    </div>
</div>
EOF

echo_color "1;32" "   - Creando app/views/dashboard/index.php"
cat << 'EOF' > app/views/dashboard/index.php
<div class="container mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Mis Planeaciones</h1>
            <p class="text-lg text-gray-600 font-roboto">Aquí puedes ver y gestionar todas tus planeaciones generadas.</p>
        </div>
        <a href="/dashboard/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nueva Planeación
        </a>
    </div>
    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-5/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre del Proyecto</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nivel y Grado</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Fecha de Creación</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($planeaciones)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <p class="text-gray-500">Aún no has creado ninguna planeación.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nueva Planeación" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planeaciones as $plan): ?>
                            <?php $prompt_data = json_decode($plan['prompt_data'], true); ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($plan['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($prompt_data['proyecto'] ?? 'Sin Título') ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($prompt_data['nivel_grado'] ?? 'No especificado') ?></td>
                                <td class="py-3 px-4"><?= date('d/m/Y H:i', strtotime($plan['fecha_creacion'])) ?></td>
                                <td class="py-3 px-4">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
EOF

echo_color "1;32" "   - Creando app/views/dashboard/create.php"
cat << 'EOF' > app/views/dashboard/create.php
<div class="container mx-auto py-10 px-6">
    <div class="flex items-center mb-8">
        <a href="/dashboard" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Crear Nueva Planeación</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-3 bg-white p-8 rounded-lg card-shadow">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Generador de Planeación Didáctica</h2>
            <form id="planeacion-form" class="space-y-6">
                <fieldset class="border p-4 rounded-md">
                    <legend class="text-lg font-semibold px-2">Datos del Docente y Escuela</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <input type="text" name="escuela" placeholder="Nombre de la Escuela" class="input-style">
                        <input type="text" name="cct" placeholder="CCT" class="input-style">
                    </div>
                </fieldset>
                <fieldset class="border p-4 rounded-md">
                    <legend class="text-lg font-semibold px-2">Marco Curricular</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <div>
                            <label for="nivel_grado" class="label-style">Nivel y Grado</label>
                            <select id="nivel_grado" name="nivel_grado" class="input-style">
                                <option value="" disabled selected>Selecciona un nivel</option>
                                <?php foreach ($niveles_grados as $nivel => $fase): ?>
                                    <option value="<?= htmlspecialchars($nivel) ?>" data-fase="<?= htmlspecialchars($fase) ?>"><?= htmlspecialchars($nivel) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="fase" id="fase">
                        </div>
                        <div>
                            <label for="contenido_pda" class="label-style">Contenido y PDA</label>
                            <select id="contenido_pda" name="contenido_pda" class="input-style" disabled>
                                <option value="" disabled selected>Primero selecciona un nivel</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-4 rounded-md">
                    <legend class="text-lg font-semibold px-2">Detalles de la Sesión</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <div>
                            <label for="num_sesiones" class="label-style">Número de Sesiones</label>
                            <input type="number" name="num_sesiones" id="num_sesiones" value="1" min="1" max="10" class="input-style">
                        </div>
                        <div>
                            <label for="duracion_sesion" class="label-style">Duración por Sesión (min)</label>
                            <input type="number" name="duracion_sesion" id="duracion_sesion" value="50" min="20" max="60" step="5" class="input-style">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="materiales" class="label-style">Materiales Disponibles</label>
                        <textarea name="materiales" id="materiales" rows="2" class="input-style" placeholder="Ej: Conos, aros, pelotas, cuerdas..."></textarea>
                    </div>
                </fieldset>
                <fieldset class="border p-4 rounded-md">
                    <legend class="text-lg font-semibold px-2">Enfoque Pedagógico</legend>
                    <label class="label-style">Ejes Articuladores</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                        <?php foreach ($ejes_articuladores as $eje): ?>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="ejes_articuladores[]" value="<?= htmlspecialchars($eje) ?>" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($eje) ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <fieldset class="border p-4 rounded-md">
                    <legend class="text-lg font-semibold px-2">Inclusión</legend>
                    <label for="alumnos_nee" class="label-style">Alumnos con Necesidades Educativas Especiales (Opcional)</label>
                    <textarea name="alumnos_nee" id="alumnos_nee" rows="2" class="input-style" placeholder="Ej: Juan - TDAH (requiere instrucciones claras y cortas). Ana - Hipoacusia (necesita apoyos visuales)."></textarea>
                </fieldset>
                <div class="pt-4 text-right">
                    <button type="submit" id="generate-btn" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                        <i class="fas fa-magic mr-2"></i>
                        Generar Planeación
                    </button>
                </div>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white rounded-lg card-shadow flex flex-col">
             <h2 class="text-2xl font-bold text-gray-800 p-8 pb-0">Respuesta de la IA</h2>
            <div id="loader" class="hidden text-center py-10 flex-grow flex flex-col justify-center items-center">
                <i class="fas fa-spinner fa-spin fa-3x text-indigo-600"></i>
                <p class="mt-4 text-gray-600 font-roboto">Diseñando sesiones... por favor, espera.</p>
            </div>
            <div id="ai-response-container" class="prose max-w-none text-gray-700 h-[80vh] overflow-y-auto p-8 font-roboto">
                <p class="text-gray-500">La planeación didáctica generada aparecerá aquí.</p>
            </div>
        </div>
    </div>
</div>
<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; }
</style>
<script>
    const contenidosPorGrado = <?= json_encode($contenidos_por_grado) ?>;
    const nivelGradoSelect = document.getElementById('nivel_grado');
    const contenidoPdaSelect = document.getElementById('contenido_pda');
    const faseInput = document.getElementById('fase');
    nivelGradoSelect.addEventListener('change', function() {
        const selectedNivel = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const contenidos = contenidosPorGrado[selectedNivel];
        faseInput.value = selectedOption.dataset.fase || '';
        contenidoPdaSelect.innerHTML = '<option value="" disabled selected>Selecciona un contenido</option>';
        contenidoPdaSelect.disabled = true;
        if (contenidos) {
            contenidoPdaSelect.disabled = false;
            for (const contenido in contenidos) {
                const pda = contenidos[contenido];
                const option = document.createElement('option');
                option.value = `${contenido}||${pda}`;
                option.textContent = contenido;
                contenidoPdaSelect.appendChild(option);
            }
        }
    });
</script>
EOF

echo_color "1;32" "   - Creando app/views/admin/index.php"
cat << 'EOF' > app/views/admin/index.php
<div class="container mx-auto py-10 px-6">
    <h1 class="text-4xl font-bold text-gray-800 mb-8">Panel de Administración</h1>
    <div class="bg-white p-8 rounded-lg card-shadow">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Gestión de Usuarios</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-2/6 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                        <th class="w-2/6 text-left py-3 px-4 uppercase font-semibold text-sm">Email</th>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">Rol</th>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">Registro</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No hay usuarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($user['id']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['nombre']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= $user['role'] === 'admin' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
EOF

# --- Scripts ---
echo_color "1;32" "   - Creando scripts/promote_user.php"
cat << 'EOF' > scripts/promote_user.php
<?php
// USO: php scripts/promote_user.php usuario@email.com
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ser ejecutado desde la terminal.\n");
}
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/models/Database.php';
if ($argc < 2) {
    echo "Error: Debes proporcionar el email del usuario a promover.\n";
    echo "Uso: php " . $argv[0] . " usuario@email.com\n";
    exit(1);
}
$email = $argv[1];
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE usuarios SET role = 'admin' WHERE email = ?");
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $db->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "¡Éxito! El usuario con el email '" . $email . "' ha sido promovido a administrador.\n";
    } else {
        echo "No se encontró ningún usuario con el email '" . $email . "'.\n";
    }
    $stmt->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
EOF

# --- Finalización ---
echo ""
echo_color "1;32" "¡Instalación completada!"
echo ""
echo_color "1;33" "--- Próximos Pasos ---"
echo_color "1;37" "1.  Importa la base de datos. Ejecuta el siguiente comando:"
echo_color "1;35" "    mysql -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < config/database.sql"
echo ""
echo_color "1;37" "2.  Configura tu Virtual Host de Apache para que el DocumentRoot apunte a la carpeta 'public' de este directorio."
echo_color "1;35" "    Ejemplo: DocumentRoot $(pwd)/public"
echo ""
echo_color "1;37" "3.  Asegúrate de que el módulo 'rewrite' de Apache esté habilitado:"
echo_color "1;35" "    sudo a2enmod rewrite && sudo systemctl restart apache2"
echo ""
echo_color "1;37" "4.  Crea tu primer usuario administrador ejecutando el script:"
echo_color "1;35" "    php scripts/promote_user.php tu-email@ejemplo.com"
echo ""
echo_color "1;34" "======================================================"
echo_color "1;34" "==                 Fin del Script                 =="
echo_color "1;34" "======================================================"
echo ""

