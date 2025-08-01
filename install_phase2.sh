#!/bin/bash

# ==============================================================================
# Script de Instalación - Fase 2: Implementación de la Pantalla "Mi Perfil"
# ==============================================================================
# Este script actualiza los controladores, vistas y JavaScript para la Fase 2
# del proyecto PlaneaIA, implementando la pantalla "Mi Perfil".
#
# USO:
# 1. Guarda este archivo como `install_phase2.sh` en el directorio raíz del proyecto.
# 2. Dale permisos de ejecución: chmod +x install_phase2.sh
# 3. Ejecútalo: ./install_phase2.sh
#

echo_color() {
    local color_code=$1
    shift
    echo -e "\033[${color_code}m$@\033[0m"
}

clear
echo_color "1;34" "======================================================"
echo_color "1;34" "==   Instalador PlaneaIA - Fase 2: Mi Perfil      =="
echo_color "1;34" "======================================================"
echo ""
echo_color "1;33" "Este script creará la pantalla 'Mi Perfil' y actualizará el dashboard y la navegación."
echo_color "1;31" "¡ADVERTENCIA! Se eliminarán y recrearán los archivos modificados."
read -p "¿Estás listo para continuar? (s/n): " confirm
if [[ "$confirm" != "s" && "$confirm" != "S" ]]; then
    echo_color "1;31" "Instalación de Fase 2 cancelada."
    exit 1
fi

# --- Paso 1: Crear directorios necesarios ---
echo_color "1;32" "-> Creando directorios necesarios..."
mkdir -p app/views/dashboard/profile
mkdir -p public/uploads/profile_pictures # Directorio para subir fotos de perfil
echo_color "1;32" "   Directorios creados."

# --- Paso 2: Crear/Reemplazar archivos PHP y JS ---

# app/controllers/DashboardController.php
echo_color "1;32" "-> Creando/Reemplazando app/controllers/DashboardController.php..."
rm -f app/controllers/DashboardController.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_DASHBOARD_CONTROLLER' > app/controllers/DashboardController.php
<?php
// Archivo: app/controllers/DashboardController.php (Fase 2)

class DashboardController {
    private $planeacionModel;
    private $paisModel;
    private $centroEducativoModel;
    private $planEstudioModel;
    private $cursoModel;
    private $contenidoCurricularModel;
    private $grupoModel;
    private $estudianteGrupoModel;
    private $userModel;
    private $asignaturaModel; // Nuevo
    private $userAsignaturaModel; // Nuevo

    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->planeacionModel = new Planeacion($db);
        $this->paisModel = new Pais($db);
        $this->centroEducativoModel = new CentroEducativo($db);
        $this->planEstudioModel = new PlanEstudio($db);
        $this->cursoModel = new Curso($db);
        $this->contenidoCurricularModel = new ContenidoCurricular($db);
        $this->grupoModel = new Grupo($db);
        $this->estudianteGrupoModel = new EstudianteGrupo($db);
        $this->userModel = new User($db);
        $this->asignaturaModel = new Asignatura($db); // Inicializar
        $this->userAsignaturaModel = new UserAsignatura($db); // Inicializar
    }

    /**
     * Muestra la página principal del dashboard con la lista de planeaciones.
     * También calcula el estado de completitud del perfil.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $page_title = 'Mi Dashboard';
        $user_id = $_SESSION['user_id'];
        $user_data = $this->userModel->getById($user_id);
        $planeaciones = $this->planeacionModel->getByUserId($user_id);
        $grupos = $this->grupoModel->getByDocenteId($user_id);

        // --- Calcular porcentaje de perfil completado ---
        $profile_completion_percentage = 0;
        // Cada bloque completo suma 25%
        // 1. Cuenta Registrada (base)
        $profile_completion_percentage += 25;

        // 2. Datos Personales
        if (!empty($user_data['apellidos']) && !empty($user_data['id_pais']) && !empty($user_data['fecha_nacimiento']) && !empty($user_data['genero'])) {
            $profile_completion_percentage += 25;
        }

        // 3. Datos Académicos (Asignaturas)
        $user_asignaturas = $this->userAsignaturaModel->getAsignaturasByUserId($user_id);
        if (!empty($user_asignaturas)) {
            $profile_completion_percentage += 25;
        }

        // 4. Foto de Perfil
        if (!empty($user_data['foto_perfil_url'])) {
            $profile_completion_percentage += 25;
        }

        // Asegurarse de que no exceda el 100%
        if ($profile_completion_percentage > 100) {
            $profile_completion_percentage = 100;
        }

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear una nueva planeación.
     */
    public function showCreateForm() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $page_title = 'Crear Nueva Planeación';
        $user_id = $_SESSION['user_id'];
        $user_data = $this->userModel->getById($user_id); // Obtener datos del usuario para pre-llenado

        $paises = $this->paisModel->getAll(true); // Solo países activos
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos para el select

        // Obtener asignaturas del docente para pre-selección
        $user_asignaturas = $this->userAsignaturaModel->getAsignaturasByUserId($user_id);
        $docente_asignaturas_ids = array_column($user_asignaturas, 'asignatura_id');
        $docente_asignaturas_nombres = array_column($user_asignaturas, 'nombre');


        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra una planeación específica.
     * @param int $id El ID de la planeación a mostrar.
     */
    public function view($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $page_title = 'Ver Planeación';

        $planeacion = $this->planeacionModel->getByIdAndUserId($id, $_SESSION['user_id']);

        if (!$planeacion) {
            header('Location: /dashboard');
            exit;
        }

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/view.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Genera y descarga el PDF de una planeación específica.
     * @param int $id El ID de la planeación.
     */
    public function generatePdf($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $planeacion = $this->planeacionModel->getByIdAndUserId($id, $_SESSION['user_id']);

        if (!$planeacion) {
            header('Location: /dashboard');
            exit;
        }

        require_once '../app/controllers/PdfController.php';
        $pdfController = new PdfController();
        $pdfController->generate($planeacion);
    }

    // --- Métodos para la Gestión de Grupos (existentes) ---
    // ... (GroupController se encarga de esto) ...

    // --- Métodos para la Pantalla "Mi Perfil" ---

    /**
     * Muestra la pantalla de perfil del usuario.
     * Ruta: /dashboard/profile
     */
    public function showProfile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $page_title = 'Mi Perfil';
        $user_id = $_SESSION['user_id'];
        $user_data = $this->userModel->getById($user_id);
        $paises = $this->paisModel->getAll(true); // Para el select de país
        $all_asignaturas = $this->asignaturaModel->getAll(true); // Todas las asignaturas activas
        $user_asignaturas_ids = array_column($this->userAsignaturaModel->getAsignaturasByUserId($user_id), 'asignatura_id');


        // Calcular porcentaje de perfil completado para mostrar en la vista del perfil
        $profile_completion_percentage = 0;
        // 1. Cuenta Registrada (base)
        $profile_completion_percentage += 25;

        // 2. Datos Personales
        if (!empty($user_data['apellidos']) && !empty($user_data['id_pais']) && !empty($user_data['fecha_nacimiento']) && !empty($user_data['genero'])) {
            $profile_completion_percentage += 25;
        }

        // 3. Datos Académicos (Asignaturas)
        if (!empty($user_asignaturas_ids)) {
            $profile_completion_percentage += 25;
        }

        // 4. Foto de Perfil
        if (!empty($user_data['foto_perfil_url'])) {
            $profile_completion_percentage += 25;
        }

        if ($profile_completion_percentage > 100) {
            $profile_completion_percentage = 100;
        }


        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/profile/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de los datos personales del usuario.
     * Ruta: /dashboard/profile/update-personal
     * Método: POST
     */
    public function processUpdatePersonalData() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $documento_dni = trim($_POST['documento_dni'] ?? '');
        $id_pais = !empty($_POST['id_pais']) ? (int)$_POST['id_pais'] : null;
        $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $genero = trim($_POST['genero'] ?? '');

        if (empty($nombre) || empty($apellidos)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Nombre y apellidos son obligatorios.'];
            header('Location: /dashboard/profile');
            exit;
        }

        if ($this->userModel->updatePersonalData($user_id, $nombre, $apellidos, $documento_dni, $id_pais, $fecha_nacimiento, $genero)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Datos personales actualizados con éxito.'];
            // Actualizar nombre en sesión si cambió
            $_SESSION['user_name'] = $nombre;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar datos personales.'];
        }
        header('Location: /dashboard/profile');
        exit;
    }

    /**
     * Procesa la actualización de las asignaturas del usuario (datos académicos).
     * Ruta: /dashboard/profile/update-academic
     * Método: POST
     */
    public function processUpdateAcademicData() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $asignaturas_seleccionadas = $_POST['asignaturas'] ?? []; // Array de IDs de asignaturas

        // Obtener asignaturas actuales del usuario
        $current_user_asignaturas_ids = array_column($this->userAsignaturaModel->getAsignaturasByUserId($user_id), 'asignatura_id');

        // Asignaturas a eliminar
        $to_remove = array_diff($current_user_asignaturas_ids, $asignaturas_seleccionadas);
        foreach ($to_remove as $asignatura_id) {
            $this->userAsignaturaModel->delete($user_id, (int)$asignatura_id);
        }

        // Asignaturas a añadir
        $to_add = array_diff($asignaturas_seleccionadas, $current_user_asignaturas_ids);
        foreach ($to_add as $asignatura_id) {
            $this->userAsignaturaModel->create($user_id, (int)$asignatura_id);
        }

        $_SESSION['message'] = ['type' => 'success', 'text' => 'Asignaturas actualizadas con éxito.'];
        header('Location: /dashboard/profile');
        exit;
    }

    /**
     * Procesa la actualización de los datos de cuenta del usuario (email, telefono, contraseña).
     * Ruta: /dashboard/profile/update-account
     * Método: POST
     */
    public function processUpdateAccountData() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $email = trim($_POST['email'] ?? '');
        $telefono_movil = trim($_POST['telefono_movil'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($email)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El correo electrónico es obligatorio.'];
            header('Location: /dashboard/profile');
            exit;
        }

        // Validar cambio de contraseña
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Las contraseñas no coinciden.'];
                header('Location: /dashboard/profile');
                exit;
            }
            if (strlen($new_password) < 6) { // Ejemplo de validación de longitud
                $_SESSION['message'] = ['type' => 'error', 'text' => 'La contraseña debe tener al menos 6 caracteres.'];
                header('Location: /dashboard/profile');
                exit;
            }
        }

        // Verificar si el nuevo email ya está en uso por otro usuario
        $existing_user_with_email = $this->userModel->findByEmail($email);
        if ($existing_user_with_email && $existing_user_with_email['id'] !== $user_id) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El correo electrónico ya está en uso por otra cuenta.'];
            header('Location: /dashboard/profile');
            exit;
        }

        $password_to_update = !empty($new_password) ? $new_password : null;

        if ($this->userModel->updateAccountData($user_id, $email, $telefono_movil, $password_to_update)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Datos de cuenta actualizados con éxito.'];
            // Actualizar email en sesión si cambió
            $_SESSION['user_email'] = $email;
        } else {
            $_SESSION['message'] = ['type' => 'info', 'text' => 'No se realizaron cambios en los datos de la cuenta.'];
        }
        header('Location: /dashboard/profile');
        exit;
    }

    /**
     * Procesa la subida y actualización de la foto de perfil.
     * Ruta: /dashboard/profile/upload-photo
     * Método: POST
     */
    public function processProfilePictureUpload() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
            $file_name = $_FILES['profile_picture']['name'];
            $file_size = $_FILES['profile_picture']['size'];
            $file_type = $_FILES['profile_picture']['type'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            if (!in_array($file_ext, $allowed_extensions)) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Tipo de archivo no permitido. Solo JPG, JPEG, PNG, GIF.'];
                header('Location: /dashboard/profile');
                exit;
            }
            if ($file_size > $max_file_size) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'El archivo es demasiado grande. Máximo 5 MB.'];
                header('Location: /dashboard/profile');
                exit;
            }

            // Crear directorio de subidas si no existe
            $upload_dir = '../public/uploads/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = uniqid('profile_') . '.' . $file_ext;
            $destination_path = $upload_dir . $new_file_name;
            $public_url = '/uploads/profile_pictures/' . $new_file_name; // URL pública para guardar en DB

            if (move_uploaded_file($file_tmp_path, $destination_path)) {
                // Eliminar foto antigua si existe
                $user_data = $this->userModel->getById($user_id);
                if ($user_data && !empty($user_data['foto_perfil_url'])) {
                    $old_file_path = '../public' . $user_data['foto_perfil_url'];
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }

                if ($this->userModel->updateProfilePicture($user_id, $public_url)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Foto de perfil actualizada con éxito.'];
                    // Actualizar URL de foto en sesión
                    $_SESSION['user_photo_url'] = $public_url;
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al guardar la URL de la foto en la base de datos.'];
                    // Si falla la DB, eliminar el archivo subido
                    unlink($destination_path);
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al subir el archivo.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'No se seleccionó ningún archivo o hubo un error en la subida.'];
        }
        header('Location: /dashboard/profile');
        exit;
    }

    /**
     * Procesa la actualización de las configuraciones de notificación del usuario.
     * Ruta: /dashboard/profile/update-notifications
     * Método: POST
     */
    public function processUpdateNotificationSettings() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $recibir_email = isset($_POST['recibir_email_notificaciones']) ? 1 : 0;
        $recibir_sms = isset($_POST['recibir_sms_notificaciones']) ? 1 : 0;
        $recibir_novedades = isset($_POST['recibir_novedades_promociones']) ? 1 : 0;

        if ($this->userModel->updateNotificationSettings($user_id, $recibir_email, $recibir_sms, $recibir_novedades)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Preferencias de notificación actualizadas con éxito.'];
            // Actualizar sesión
            $_SESSION['user_data']['recibir_email_notificaciones'] = $recibir_email;
            $_SESSION['user_data']['recibir_sms_notificaciones'] = $recibir_sms;
            $_SESSION['user_data']['recibir_novedades_promociones'] = $recibir_novedades;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar preferencias de notificación.'];
        }
        header('Location: /dashboard/profile');
        exit;
    }
}
EOF_PHP_DASHBOARD_CONTROLLER

# app/views/dashboard/profile/index.php
echo_color "1;32" "-> Creando app/views/dashboard/profile/index.php..."
rm -f app/views/dashboard/profile/index.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_PROFILE_INDEX' > app/views/dashboard/profile/index.php
<?php
// app/views/dashboard/profile/index.php (Fase 2)

// Mensajes de sesión (éxito/error)
if (isset($_SESSION['message'])): ?>
    <div class="container mx-auto px-6 mt-8">
        <div class="p-4 rounded-md <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
        </div>
    </div>
    <?php unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
endif;
?>

<div class="container mx-auto py-10 px-6">
    <div class="flex items-center mb-8">
        <a href="/dashboard" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Mi Perfil</h1>
    </div>

    <!-- Barra de Progreso del Perfil -->
    <div class="bg-white p-6 rounded-lg card-shadow mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-tasks mr-3 text-indigo-600"></i> Estado de Completitud del Perfil
        </h2>
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
            <div class="bg-indigo-600 h-4 rounded-full" style="width: <?= htmlspecialchars($profile_completion_percentage) ?>%;"></div>
        </div>
        <p class="text-lg text-gray-700 text-center">
            Perfil Completado: <span class="font-semibold"><?= htmlspecialchars($profile_completion_percentage) ?>%</span>
        </p>
        <?php if ($profile_completion_percentage < 100): ?>
            <p class="text-center text-gray-600 mt-2">
                ¡Completa tu perfil para aprovechar al máximo todas las funcionalidades!
            </p>
        <?php else: ?>
            <p class="text-center text-green-600 font-semibold mt-2">
                ¡Tu perfil está 100% completo!
            </p>
        <?php endif; ?>
    </div>

    <!-- Bloque 1: Datos Personales -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-circle mr-3 text-blue-600"></i> Datos Personales
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="personal">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-personal" action="/dashboard/profile/update-personal" method="POST" class="space-y-4 mt-4 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="label-style">Nombre(s)</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($user_data['nombre'] ?? '') ?>" required class="input-style">
                </div>
                <div>
                    <label for="apellidos" class="label-style">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" value="<?= htmlspecialchars($user_data['apellidos'] ?? '') ?>" required class="input-style">
                </div>
            </div>
            <div>
                <label for="documento_dni" class="label-style">Documento / DNI (Opcional)</label>
                <input type="text" name="documento_dni" id="documento_dni" value="<?= htmlspecialchars($user_data['documento_dni'] ?? '') ?>" class="input-style">
            </div>
            <div>
                <label for="id_pais" class="label-style">País</label>
                <select name="id_pais" id="id_pais" class="input-style">
                    <option value="">Selecciona tu país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?= htmlspecialchars($pais['id']) ?>" <?= (isset($user_data['id_pais']) && $user_data['id_pais'] == $pais['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pais['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fecha_nacimiento" class="label-style">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= htmlspecialchars($user_data['fecha_nacimiento'] ?? '') ?>" class="input-style">
            </div>
            <div>
                <label for="genero" class="label-style">Género</label>
                <select name="genero" id="genero" class="input-style">
                    <option value="">Selecciona tu género</option>
                    <option value="masculino" <?= (isset($user_data['genero']) && $user_data['genero'] == 'masculino') ? 'selected' : '' ?>>Masculino</option>
                    <option value="femenino" <?= (isset($user_data['genero']) && $user_data['genero'] == 'femenino') ? 'selected' : '' ?>>Femenino</option>
                    <option value="otro" <?= (isset($user_data['genero']) && $user_data['genero'] == 'otro') ? 'selected' : '' ?>>Otro</option>
                    <option value="prefiero_no_decir" <?= (isset($user_data['genero']) && $user_data['genero'] == 'prefiero_no_decir') ? 'selected' : '' ?>>Prefiero no decir</option>
                </select>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="personal">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-blue-600 text-white hover:bg-blue-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-personal" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Nombre Completo:</span> <?= htmlspecialchars($user_data['nombre'] ?? '') ?> <?= htmlspecialchars($user_data['apellidos'] ?? '') ?></p>
            <p><span class="font-semibold">Documento/DNI:</span> <?= htmlspecialchars($user_data['documento_dni'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">País:</span> <?= htmlspecialchars($user_data['nombre_pais'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Fecha de Nacimiento:</span> <?= htmlspecialchars($user_data['fecha_nacimiento'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Género:</span> <?= htmlspecialchars(ucfirst($user_data['genero'] ?? 'No especificado')) ?></p>
        </div>
    </div>

    <!-- Bloque 2: Datos Académicos -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-graduation-cap mr-3 text-green-600"></i> Datos Académicos
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="academic">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-academic" action="/dashboard/profile/update-academic" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="label-style">Asignaturas que enseñas:</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2 max-h-48 overflow-y-auto border p-3 rounded-md bg-gray-50">
                    <?php if (empty($all_asignaturas)): ?>
                        <p class="text-gray-500 col-span-full">No hay asignaturas disponibles para seleccionar.</p>
                    <?php else: ?>
                        <?php foreach ($all_asignaturas as $asignatura): ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="asignaturas[]" value="<?= htmlspecialchars($asignatura['id']) ?>"
                                    <?= in_array($asignatura['id'], $user_asignaturas_ids) ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($asignatura['nombre']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="academic">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-green-600 text-white hover:bg-green-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-academic" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Asignaturas:</span>
                <?php if (empty($user_asignaturas_ids)): ?>
                    No especificadas
                <?php else: ?>
                    <?= htmlspecialchars(implode(', ', array_column($user_asignaturas, 'nombre'))) ?>
                <?php endif; ?>
            </p>
            <p class="mt-4">
                <span class="font-semibold">Administrar Grupos y Estudiantes:</span>
                <a href="/dashboard/grupos" class="text-indigo-600 hover:text-indigo-800 ml-2">Ir a Grupos <i class="fas fa-arrow-right ml-1"></i></a>
            </p>
        </div>
    </div>

    <!-- Bloque 3: Cuenta en la Plataforma -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-id-card-alt mr-3 text-purple-600"></i> Cuenta en la Plataforma
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="account">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-account" action="/dashboard/profile/update-account" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label for="email" class="label-style">Correo Electrónico</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required class="input-style">
            </div>
            <div>
                <label for="telefono_movil" class="label-style">Número de Móvil (Opcional)</label>
                <input type="tel" name="telefono_movil" id="telefono_movil" value="<?= htmlspecialchars($user_data['telefono_movil'] ?? '') ?>" class="input-style" placeholder="Ej: +52 123 456 7890">
            </div>
            <div class="mt-4">
                <label for="new_password" class="label-style">Nueva Contraseña (Dejar vacío para no cambiar)</label>
                <input type="password" name="new_password" id="new_password" class="input-style" placeholder="********">
            </div>
            <div>
                <label for="confirm_password" class="label-style">Confirmar Nueva Contraseña</label>
                <input type="password" name="confirm_password" id="confirm_password" class="input-style" placeholder="********">
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="account">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-purple-600 text-white hover:bg-purple-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-account" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Correo Electrónico:</span> <?= htmlspecialchars($user_data['email'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Número de Móvil:</span> <?= htmlspecialchars($user_data['telefono_movil'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Contraseña:</span> ********</p>
            <div class="mt-4 flex items-center">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden mr-4">
                    <?php if (!empty($user_data['foto_perfil_url'])): ?>
                        <img src="<?= htmlspecialchars($user_data['foto_perfil_url']) ?>" alt="Foto de Perfil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user text-4xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                <form id="form-profile-picture" action="/dashboard/profile/upload-photo" method="POST" enctype="multipart/form-data" class="flex items-center">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden">
                    <label for="profile_picture" class="bg-indigo-500 text-white px-4 py-2 rounded-md cursor-pointer hover:bg-indigo-600 transition-colors text-sm font-semibold">
                        <i class="fas fa-upload mr-2"></i> Subir Foto
                    </label>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md ml-2 hover:bg-blue-600 transition-colors text-sm font-semibold hidden" id="save_profile_picture_btn">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bloque 4: Notificaciones de la Plataforma -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-bell mr-3 text-orange-600"></i> Notificaciones de la Plataforma
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="notifications">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-notifications" action="/dashboard/profile/update-notifications" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_email_notificaciones" value="1" <?= ($user_data['recibir_email_notificaciones'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir notificaciones por correo electrónico</span>
                </label>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_sms_notificaciones" value="1" <?= ($user_data['recibir_sms_notificaciones'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir notificaciones por SMS</span>
                </label>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_novedades_promociones" value="1" <?= ($user_data['recibir_novedades_promociones'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir novedades, noticias y promociones de la plataforma</span>
                </label>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="notifications">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-orange-600 text-white hover:bg-orange-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-notifications" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Correo Electrónico:</span> <?= ($user_data['recibir_email_notificaciones'] ?? true) ? 'Sí' : 'No' ?></p>
            <p><span class="font-semibold">SMS:</span> <?= ($user_data['recibir_sms_notificaciones'] ?? false) ? 'Sí' : 'No' ?></p>
            <p><span class="font-semibold">Novedades/Promociones:</span> <?= ($user_data['recibir_novedades_promociones'] ?? true) ? 'Sí' : 'No' ?></p>
        </div>
    </div>

</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    .btn-save-edit { padding: 0.625rem 1.25rem; border-radius: 0.375rem; font-weight: 600; transition: background-color 0.3s ease; }
</style>

<script src="/js/profile.js"></script>
EOF_PHP_PROFILE_INDEX

# public/index.php
echo_color "1;32" "-> Creando/Reemplazando public/index.php..."
rm -f public/index.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_INDEX' > public/index.php
<?php
// Archivo: public/index.php (Front Controller - Fase 2)
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

// --- Protección de Rutas ---
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$is_docente = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'docente';

// Rutas de administración (solo para admins)
if (strpos($route, 'admin/') === 0 && !$is_admin) {
    http_response_code(403);
    die('Acceso Prohibido');
}

// Rutas protegidas para usuarios logueados (docentes y administradores)
$protected_routes_base = ['dashboard', 'api', 'grupos']; // 'grupos' como base para /dashboard/grupos*

// Rutas protegidas exactas
$protected_exact_routes = [
    'dashboard/create', 'dashboard/view', 'dashboard/pdf',
    'api/generate',
    'api/centros-educativos',
    'api/planes-estudio',
    'api/cursos',
    'api/contenidos-curriculares',
    'api/grupos',
    'api/grupo-estudiantes',
    'api/formulario-dinamico-by-curso',
    'dashboard/grupos/create',
    'dashboard/grupos/process-create',
    'dashboard/grupos/process-update',
    'dashboard/grupos/process-delete',
    'dashboard/profile', // Nueva ruta de perfil
    'dashboard/profile/update-personal', // Nueva ruta para actualizar datos personales
    'dashboard/profile/update-academic', // Nueva ruta para actualizar datos académicos
    'dashboard/profile/update-account', // Nueva ruta para actualizar datos de cuenta
    'dashboard/profile/upload-photo', // Nueva ruta para subir foto de perfil
    'dashboard/profile/update-notifications' // Nueva ruta para actualizar notificaciones
];


// Extraer la base de la ruta para la protección
$route_base = explode('/', $route)[0];

if ((in_array($route_base, $protected_routes_base) || in_array($route, $protected_exact_routes)) && !$is_logged_in) {
    if (strpos($route, 'api/') === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    } else {
        header('Location: /login');
    }
    exit;
}

// --- Lógica de enrutamiento ---
$route_parts = explode('/', rtrim($route, '/'));

switch ($route_parts[0]) {
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
        if (isset($route_parts[1])) {
            switch ($route_parts[1]) {
                case 'create':
                    (new DashboardController())->showCreateForm();
                    break;
                case 'view':
                    if (isset($route_parts[2]) && is_numeric($route_parts[2])) {
                        (new DashboardController())->view((int)$route_parts[2]);
                    } else {
                        http_response_code(404);
                        echo "<h1>404 - Planeación No Encontrada</h1>";
                    }
                    break;
                case 'pdf':
                    if (isset($route_parts[2]) && is_numeric($route_parts[2])) {
                        (new DashboardController())->generatePdf((int)$route_parts[2]);
                    } else {
                        http_response_code(404);
                        echo "<h1>404 - PDF No Encontrado</h1>";
                    }
                    break;
                case 'grupos': // Rutas para la gestión de grupos
                    // Asegurarse de que solo los docentes puedan acceder a la gestión de grupos
                    if (!$is_docente && !$is_admin) { // Admin también puede gestionar grupos
                        http_response_code(403);
                        die('Acceso Prohibido');
                    }
                    $groupController = new GroupController();
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $groupController->showCreateForm();
                                break;
                            case 'process-create':
                                $groupController->processCreate();
                                break;
                            case 'view':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $groupController->view((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Grupo No Encontrado</h1>";
                                }
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $groupController->showEditForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Grupo No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $groupController->processUpdate((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Grupo</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $groupController->processDelete((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Grupo</h1>";
                                }
                                break;
                            default:
                                $groupController->index(); // /dashboard/grupos sin sub-ruta
                                break;
                        }
                    } else {
                        $groupController->index(); // /dashboard/grupos
                    }
                    break;
                case 'profile': // Nuevas rutas de perfil
                    if (!$is_docente && !$is_admin) {
                        http_response_code(403);
                        die('Acceso Prohibido');
                    }
                    $dashboardController = new DashboardController(); // Reutilizamos el mismo controlador
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'update-personal':
                                $dashboardController->processUpdatePersonalData();
                                break;
                            case 'update-academic':
                                $dashboardController->processUpdateAcademicData();
                                break;
                            case 'update-account':
                                $dashboardController->processUpdateAccountData();
                                break;
                            case 'upload-photo':
                                $dashboardController->processProfilePictureUpload();
                                break;
                            case 'update-notifications':
                                $dashboardController->processUpdateNotificationSettings();
                                break;
                            default:
                                $dashboardController->showProfile(); // /dashboard/profile sin sub-ruta
                                break;
                        }
                    } else {
                        $dashboardController->showProfile(); // /dashboard/profile
                    }
                    break;
                default:
                    (new DashboardController())->index();
                    break;
            }
        } else {
            (new DashboardController())->index();
        }
        break;
    case 'api':
        // Asegurarse de que solo métodos POST para 'generate' y GET para las APIs de datos
        $apiController = new ApiController();
        switch ($route_parts[1] ?? '') {
            case 'generate':
                $apiController->generatePlaneacion();
                break;
            case 'centros-educativos':
                $apiController->getCentrosEducativos();
                break;
            case 'planes-estudio':
                $apiController->getPlanesEstudio();
                break;
            case 'cursos':
                $apiController->getCursos();
                break;
            case 'contenidos-curriculares':
                $apiController->getContenidosCurriculares();
                break;
            case 'grupos':
                $apiController->getGrupos();
                break;
            case 'grupo-estudiantes':
                $apiController->getGrupoEstudiantes();
                break;
            case 'formulario-dinamico-by-curso':
                $apiController->getFormularioDinamicoByCurso();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API Endpoint no encontrado.']);
                break;
        }
        break;
    case 'admin':
        if (isset($route_parts[1])) {
            $adminController = new AdminController();
            switch ($route_parts[1]) {
                case 'dashboard':
                    $adminController->index();
                    break;
                case 'paises': // Rutas para la gestión de países
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreatePaisForm();
                                break;
                            case 'process-create':
                                $adminController->processCreatePais();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditPaisForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - País No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdatePais((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar País</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeletePais((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar País</h1>";
                                }
                                break;
                            default:
                                $adminController->listPaises(); // /admin/paises sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listPaises(); // /admin/paises
                    }
                    break;
                case 'centros-educativos': // Rutas para la gestión de centros educativos
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreateCentroEducativoForm();
                                break;
                            case 'process-create':
                                $adminController->processCreateCentroEducativo();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditCentroEducativoForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Centro Educativo No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdateCentroEducativo((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Centro Educativo</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeleteCentroEducativo((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Centro Educativo</h1>";
                                }
                                break;
                            default:
                                $adminController->listCentrosEducativos(); // /admin/centros-educativos sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listCentrosEducativos(); // /admin/centros-educativos
                    }
                    break;
                case 'planes-estudio': // Rutas para la gestión de planes de estudio
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreatePlanEstudioForm();
                                break;
                            case 'process-create':
                                $adminController->processCreatePlanEstudio();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditPlanEstudioForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Plan de Estudio No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdatePlanEstudio((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Plan de Estudio</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeletePlanEstudio((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Plan de Estudio</h1>";
                                }
                                break;
                            default:
                                $adminController->listPlanesEstudio(); // /admin/planes-estudio sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listPlanesEstudio(); // /admin/planes-estudio
                    }
                    break;
                case 'cursos': // Rutas para la gestión de cursos
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreateCursoForm();
                                break;
                            case 'process-create':
                                $adminController->processCreateCurso();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditCursoForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Curso No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdateCurso((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Curso</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeleteCurso((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Curso</h1>";
                                }
                                break;
                            default:
                                $adminController->listCursos(); // /admin/cursos sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listCursos(); // /admin/cursos
                    }
                    break;
                case 'contenidos-curriculares': // Rutas para la gestión de contenidos curriculares
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreateContenidoCurricularForm();
                                break;
                            case 'process-create':
                                $adminController->processCreateContenidoCurricular();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditContenidoCurricularForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Contenido Curricular No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdateContenidoCurricular((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Contenido Curricular</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeleteContenidoCurricular((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Contenido Curricular</h1>";
                                }
                                break;
                            default:
                                $adminController->listContenidosCurriculares(); // /admin/contenidos-curriculares sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listContenidosCurriculares(); // /admin/contenidos-curriculares
                    }
                    break;
                case 'formularios-dinamicos': // Rutas para la gestión de formularios dinámicos
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreateFormularioDinamicoForm();
                                break;
                            case 'process-create':
                                $adminController->processCreateFormularioDinamico();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditFormularioDinamicoForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Formulario Dinámico No Encontrado</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdateFormularioDinamico((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Formulario Dinámico</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeleteFormularioDinamico((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Formulario Dinámico</h1>";
                                }
                                break;
                            default:
                                $adminController->listFormulariosDinamicos(); // /admin/formularios-dinamicos sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listFormulariosDinamicos(); // /admin/formularios-dinamicos
                    }
                    break;
                case 'asistentes-ia': // Rutas para la gestión de configuraciones de asistentes IA
                    if (isset($route_parts[2])) {
                        switch ($route_parts[2]) {
                            case 'create':
                                $adminController->showCreateOpenAIAssistantConfigForm();
                                break;
                            case 'process-create':
                                $adminController->processCreateOpenAIAssistantConfig();
                                break;
                            case 'edit':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->showEditOpenAIAssistantConfigForm((int)$route_parts[3]);
                                } else {
                                    http_response_code(404);
                                    echo "<h1>404 - Configuración de Asistente IA No Encontrada</h1>";
                                }
                                break;
                            case 'process-update':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processUpdateOpenAIAssistantConfig((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Actualizar Configuración de Asistente IA</h1>";
                                }
                                break;
                            case 'process-delete':
                                if (isset($route_parts[3]) && is_numeric($route_parts[3])) {
                                    $adminController->processDeleteOpenAIAssistantConfig((int)$route_parts[3]);
                                } else {
                                    http_response_code(400);
                                    echo "<h1>400 - Solicitud Incorrecta para Eliminar Configuración de Asistente IA</h1>";
                                }
                                break;
                            default:
                                $adminController->listOpenAIAssistantConfigs(); // /admin/asistentes-ia sin sub-ruta
                                break;
                        }
                    } else {
                        $adminController->listOpenAIAssistantConfigs(); // /admin/asistentes-ia
                    }
                    break;
                default:
                    http_response_code(404);
                    echo "<h1>404 - Página No Encontrada</h1>";
                    break;
            }
        } else {
            (new AdminController())->index();
        }
        break;
    default:
        http_response_code(404);
        echo "<h1>404 - Página No Encontrada</h1>";
        break;
}
EOF_PHP_INDEX

# app/views/_partials/header.php
echo_color "1;32" "-> Creando/Reemplazando app/views/_partials/header.php..."
rm -f app/views/_partials/header.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_HEADER' > app/views/_partials/header.php
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
        /* Estilos para el avatar de perfil */
        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0; /* bg-gray-200 */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #a78bfa; /* purple-400 */
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-avatar i {
            color: #64748b; /* gray-500 */
            font-size: 1.5rem;
        }
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
                    <!-- Enlace a Mi Perfil con icono de avatar -->
                    <a href="/dashboard/profile" class="flex items-center text-gray-600 hover:text-indigo-600 font-medium">
                        <div class="profile-avatar mr-2">
                            <?php if (isset($_SESSION['user_photo_url']) && !empty($_SESSION['user_photo_url'])): ?>
                                <img src="<?= htmlspecialchars($_SESSION['user_photo_url']) ?>" alt="Foto de Perfil">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        Mi Perfil
                    </a>
                    <a href="/logout" class="bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors duration-300 text-sm font-semibold">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/login" class="text-gray-600 hover:text-indigo-600 font-medium">Iniciar Sesión</a>
                    <a href="/register" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors duration-300 text-sm font-semibold">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>
        <?php
        // Mensajes de sesión (éxito/error) - Se muestran en el header para todas las páginas
        if (isset($_SESSION['message'])): ?>
            <div class="container mx-auto px-6 mt-8">
                <div class="p-4 rounded-md <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
                </div>
            </div>
            <?php unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
        endif;
        ?>
EOF_PHP_HEADER

# public/js/profile.js
echo_color "1;32" "-> Creando public/js/profile.js..."
rm -f public/js/profile.js # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_JS_PROFILE' > public/js/profile.js
// public/js/profile.js (Fase 2)

document.addEventListener('DOMContentLoaded', function() {
    // Lógica para mostrar/ocultar formularios de edición de bloques
    const editButtons = document.querySelectorAll('.edit-block-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const blockId = this.dataset.block;
            const form = document.getElementById(`form-${blockId}`);
            const displayDiv = document.getElementById(`display-${blockId}`);

            if (form && displayDiv) {
                form.classList.toggle('hidden');
                displayDiv.classList.toggle('hidden');
                // Cambiar texto del botón
                if (form.classList.contains('hidden')) {
                    this.innerHTML = '<i class="fas fa-edit mr-1"></i> Editar';
                } else {
                    this.innerHTML = '<i class="fas fa-times mr-1"></i> Cancelar';
                }
            }
        });
    });

    // Lógica para el botón de cancelar en los formularios de edición
    const cancelButtons = document.querySelectorAll('.btn-cancel-edit');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const blockId = this.dataset.block;
            const form = document.getElementById(`form-${blockId}`);
            const displayDiv = document.getElementById(`display-${blockId}`);
            const editBtn = document.querySelector(`.edit-block-btn[data-block="${blockId}"]`);

            if (form && displayDiv && editBtn) {
                form.classList.add('hidden');
                displayDiv.classList.remove('hidden');
                editBtn.innerHTML = '<i class="fas fa-edit mr-1"></i> Editar';
                // Opcional: resetear el formulario a sus valores iniciales si se cancela
                form.reset();
            }
        });
    });

    // Lógica para la subida de foto de perfil
    const profilePictureInput = document.getElementById('profile_picture');
    const saveProfilePictureBtn = document.getElementById('save_profile_picture_btn');
    const profilePictureForm = document.getElementById('form-profile-picture');

    if (profilePictureInput && saveProfilePictureBtn && profilePictureForm) {
        profilePictureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                saveProfilePictureBtn.classList.remove('hidden'); // Mostrar botón de guardar
            } else {
                saveProfilePictureBtn.classList.add('hidden'); // Ocultar si no hay archivo
            }
        });

        // Opcional: Previsualización de la imagen antes de subir
        // const profileAvatarImg = document.querySelector('.profile-avatar img');
        // if (profileAvatarImg) {
        //     profilePictureInput.addEventListener('change', function() {
        //         if (this.files && this.files[0]) {
        //             const reader = new FileReader();
        //             reader.onload = function(e) {
        //                 profileAvatarImg.src = e.target.result;
        //             };
        //             reader.readAsDataURL(this.files[0]);
        //         }
        //     });
        // }
    }
});
EOF_JS_PROFILE

# app/views/dashboard/profile/index.php
echo_color "1;32" "-> Creando app/views/dashboard/profile/index.php..."
rm -f app/views/dashboard/profile/index.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_PROFILE_INDEX_VIEW' > app/views/dashboard/profile/index.php
<?php
// app/views/dashboard/profile/index.php (Fase 2)

// Mensajes de sesión (éxito/error)
if (isset($_SESSION['message'])): ?>
    <div class="container mx-auto px-6 mt-8">
        <div class="p-4 rounded-md <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
        </div>
    </div>
    <?php unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
endif;
?>

<div class="container mx-auto py-10 px-6">
    <div class="flex items-center mb-8">
        <a href="/dashboard" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Mi Perfil</h1>
    </div>

    <!-- Barra de Progreso del Perfil -->
    <div class="bg-white p-6 rounded-lg card-shadow mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-tasks mr-3 text-indigo-600"></i> Estado de Completitud del Perfil
        </h2>
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
            <div class="bg-indigo-600 h-4 rounded-full" style="width: <?= htmlspecialchars($profile_completion_percentage) ?>%;"></div>
        </div>
        <p class="text-lg text-gray-700 text-center">
            Perfil Completado: <span class="font-semibold"><?= htmlspecialchars($profile_completion_percentage) ?>%</span>
        </p>
        <?php if ($profile_completion_percentage < 100): ?>
            <p class="text-center text-gray-600 mt-2">
                ¡Completa tu perfil para aprovechar al máximo todas las funcionalidades!
            </p>
        <?php else: ?>
            <p class="text-center text-green-600 font-semibold mt-2">
                ¡Tu perfil está 100% completo!
            </p>
        <?php endif; ?>
    </div>

    <!-- Bloque 1: Datos Personales -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-circle mr-3 text-blue-600"></i> Datos Personales
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="personal">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-personal" action="/dashboard/profile/update-personal" method="POST" class="space-y-4 mt-4 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="label-style">Nombre(s)</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($user_data['nombre'] ?? '') ?>" required class="input-style">
                </div>
                <div>
                    <label for="apellidos" class="label-style">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" value="<?= htmlspecialchars($user_data['apellidos'] ?? '') ?>" required class="input-style">
                </div>
            </div>
            <div>
                <label for="documento_dni" class="label-style">Documento / DNI (Opcional)</label>
                <input type="text" name="documento_dni" id="documento_dni" value="<?= htmlspecialchars($user_data['documento_dni'] ?? '') ?>" class="input-style">
            </div>
            <div>
                <label for="id_pais" class="label-style">País</label>
                <select name="id_pais" id="id_pais" class="input-style">
                    <option value="">Selecciona tu país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?= htmlspecialchars($pais['id']) ?>" <?= (isset($user_data['id_pais']) && $user_data['id_pais'] == $pais['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pais['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fecha_nacimiento" class="label-style">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= htmlspecialchars($user_data['fecha_nacimiento'] ?? '') ?>" class="input-style">
            </div>
            <div>
                <label for="genero" class="label-style">Género</label>
                <select name="genero" id="genero" class="input-style">
                    <option value="">Selecciona tu género</option>
                    <option value="masculino" <?= (isset($user_data['genero']) && $user_data['genero'] == 'masculino') ? 'selected' : '' ?>>Masculino</option>
                    <option value="femenino" <?= (isset($user_data['genero']) && $user_data['genero'] == 'femenino') ? 'selected' : '' ?>>Femenino</option>
                    <option value="otro" <?= (isset($user_data['genero']) && $user_data['genero'] == 'otro') ? 'selected' : '' ?>>Otro</option>
                    <option value="prefiero_no_decir" <?= (isset($user_data['genero']) && $user_data['genero'] == 'prefiero_no_decir') ? 'selected' : '' ?>>Prefiero no decir</option>
                </select>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="personal">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-blue-600 text-white hover:bg-blue-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-personal" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Nombre Completo:</span> <?= htmlspecialchars($user_data['nombre'] ?? '') ?> <?= htmlspecialchars($user_data['apellidos'] ?? '') ?></p>
            <p><span class="font-semibold">Documento/DNI:</span> <?= htmlspecialchars($user_data['documento_dni'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">País:</span> <?= htmlspecialchars($user_data['nombre_pais'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Fecha de Nacimiento:</span> <?= htmlspecialchars($user_data['fecha_nacimiento'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Género:</span> <?= htmlspecialchars(ucfirst($user_data['genero'] ?? 'No especificado')) ?></p>
        </div>
    </div>

    <!-- Bloque 2: Datos Académicos -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-graduation-cap mr-3 text-green-600"></i> Datos Académicos
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="academic">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-academic" action="/dashboard/profile/update-academic" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="label-style">Asignaturas que enseñas:</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2 max-h-48 overflow-y-auto border p-3 rounded-md bg-gray-50">
                    <?php if (empty($all_asignaturas)): ?>
                        <p class="text-gray-500 col-span-full">No hay asignaturas disponibles para seleccionar.</p>
                    <?php else: ?>
                        <?php foreach ($all_asignaturas as $asignatura): ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="asignaturas[]" value="<?= htmlspecialchars($asignatura['id']) ?>"
                                    <?= in_array($asignatura['id'], $user_asignaturas_ids) ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($asignatura['nombre']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="academic">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-green-600 text-white hover:bg-green-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-academic" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Asignaturas:</span>
                <?php if (empty($user_asignaturas_ids)): ?>
                    No especificadas
                <?php else: ?>
                    <?= htmlspecialchars(implode(', ', array_column($user_asignaturas, 'nombre'))) ?>
                <?php endif; ?>
            </p>
            <p class="mt-4">
                <span class="font-semibold">Administrar Grupos y Estudiantes:</span>
                <a href="/dashboard/grupos" class="text-indigo-600 hover:text-indigo-800 ml-2">Ir a Grupos <i class="fas fa-arrow-right ml-1"></i></a>
            </p>
        </div>
    </div>

    <!-- Bloque 3: Cuenta en la Plataforma -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-id-card-alt mr-3 text-purple-600"></i> Cuenta en la Plataforma
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="account">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-account" action="/dashboard/profile/update-account" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label for="email" class="label-style">Correo Electrónico</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required class="input-style">
            </div>
            <div>
                <label for="telefono_movil" class="label-style">Número de Móvil (Opcional)</label>
                <input type="tel" name="telefono_movil" id="telefono_movil" value="<?= htmlspecialchars($user_data['telefono_movil'] ?? '') ?>" class="input-style" placeholder="Ej: +52 123 456 7890">
            </div>
            <div class="mt-4">
                <label for="new_password" class="label-style">Nueva Contraseña (Dejar vacío para no cambiar)</label>
                <input type="password" name="new_password" id="new_password" class="input-style" placeholder="********">
            </div>
            <div>
                <label for="confirm_password" class="label-style">Confirmar Nueva Contraseña</label>
                <input type="password" name="confirm_password" id="confirm_password" class="input-style" placeholder="********">
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="account">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-purple-600 text-white hover:bg-purple-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-account" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Correo Electrónico:</span> <?= htmlspecialchars($user_data['email'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Número de Móvil:</span> <?= htmlspecialchars($user_data['telefono_movil'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Contraseña:</span> ********</p>
            <div class="mt-4 flex items-center">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden mr-4">
                    <?php if (!empty($user_data['foto_perfil_url'])): ?>
                        <img src="<?= htmlspecialchars($user_data['foto_perfil_url']) ?>" alt="Foto de Perfil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user text-4xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                <form id="form-profile-picture" action="/dashboard/profile/upload-photo" method="POST" enctype="multipart/form-data" class="flex items-center">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden">
                    <label for="profile_picture" class="bg-indigo-500 text-white px-4 py-2 rounded-md cursor-pointer hover:bg-indigo-600 transition-colors text-sm font-semibold">
                        <i class="fas fa-upload mr-2"></i> Subir Foto
                    </label>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md ml-2 hover:bg-blue-600 transition-colors text-sm font-semibold hidden" id="save_profile_picture_btn">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bloque 4: Notificaciones de la Plataforma -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-bell mr-3 text-orange-600"></i> Notificaciones de la Plataforma
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="notifications">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-notifications" action="/dashboard/profile/update-notifications" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_email_notificaciones" value="1" <?= ($user_data['recibir_email_notificaciones'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir notificaciones por correo electrónico</span>
                </label>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_sms_notificaciones" value="1" <?= ($user_data['recibir_sms_notificaciones'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir notificaciones por SMS</span>
                </label>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_novedades_promociones" value="1" <?= ($user_data['recibir_novedades_promociones'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir novedades, noticias y promociones de la plataforma</span>
                </label>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="notifications">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-orange-600 text-white hover:bg-orange-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-notifications" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Correo Electrónico:</span> <?= ($user_data['recibir_email_notificaciones'] ?? true) ? 'Sí' : 'No' ?></p>
            <p><span class="font-semibold">SMS:</span> <?= ($user_data['recibir_sms_notificaciones'] ?? false) ? 'Sí' : 'No' ?></p>
            <p><span class="font-semibold">Novedades/Promociones:</span> <?= ($user_data['recibir_novedades_promociones'] ?? true) ? 'Sí' : 'No' ?></p>
        </div>
    </div>

</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    .btn-save-edit { padding: 0.625rem 1.25rem; border-radius: 0.375rem; font-weight: 600; transition: background-color 0.3s ease; }
</style>

<script src="/js/profile.js"></script>
EOF_PHP_PROFILE_INDEX_VIEW