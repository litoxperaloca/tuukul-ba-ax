#!/bin/bash

# ==============================================================================
# Script de Instalación - Fase 4: Rediseño de la Pantalla "Crear Planeación"
# ==============================================================================
# Este script actualiza los controladores y vistas para la Fase 4 del proyecto PlaneaIA,
# implementando el formulario de creación de planeación multi-paso.
#
# USO:
# 1. Guarda este archivo como `install_phase4.sh` en el directorio raíz del proyecto.
# 2. Dale permisos de ejecución: chmod +x install_phase4.sh
# 3. Ejecútalo: ./install_phase4.sh
#

echo_color() {
    local color_code=$1
    shift
    echo -e "\033[${color_code}m$@\033[0m"
}

clear
echo_color "1;34" "======================================================"
echo_color "1;34" "==  Instalador PlaneaIA - Fase 4: Crear Planeación =="
echo_color "1;34" "======================================================"
echo ""
echo_color "1;33" "Este script actualizará la pantalla de creación de planeaciones a un formulario multi-paso."
echo_color "1;31" "¡ADVERTENCIA! Se eliminarán y recrearán los archivos modificados."
read -p "¿Estás listo para continuar? (s/n): " confirm
if [[ "$confirm" != "s" && "$confirm" != "S" ]]; then
    echo_color "1;31" "Instalación de Fase 4 cancelada."
    exit 1
fi

# --- Paso 1: Crear directorios necesarios (si aplica) ---
echo_color "1;32" "-> Creando directorios necesarios (si aplica)..."
# No se necesitan nuevos directorios específicos para esta fase
echo_color "1;32" "   Directorios verificados."

# --- Paso 2: Crear/Reemplazar archivos PHP y JS ---

# app/controllers/DashboardController.php
echo_color "1;32" "-> Creando/Reemplazando app/controllers/DashboardController.php..."
rm -f app/controllers/DashboardController.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_DASHBOARD_CONTROLLER' > app/controllers/DashboardController.php
<?php
// Archivo: app/controllers/DashboardController.php (Fase 4)

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
    private $asignaturaModel;
    private $userAsignaturaModel;

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
        $this->asignaturaModel = new Asignatura($db);
        $this->userAsignaturaModel = new UserAsignatura($db);
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
        $user_asignaturas_data = $this->userAsignaturaModel->getAsignaturasByUserId($user_id);
        $user_asignaturas_ids = array_column($user_asignaturas_data, 'asignatura_id');
        $user_asignaturas_nombres = array_column($user_asignaturas_data, 'nombre');

        // Determinar la asignatura a pre-seleccionar o la lista a mostrar
        $selected_asignatura_id = null;
        if (count($user_asignaturas_data) === 1) {
            $selected_asignatura_id = $user_asignaturas_data[0]['asignatura_id'];
        }

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

# app/views/dashboard/create.php
echo_color "1;32" "-> Creando/Reemplazando app/views/dashboard/create.php..."
rm -f app/views/dashboard/create.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_CREATE_VIEW' > app/views/dashboard/create.php
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
                <!-- Indicador de pasos -->
                <div class="flex justify-between items-center mb-8">
                    <div class="flex-1 text-center">
                        <div id="step-indicator-1" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-indigo-600 text-white font-bold">1</div>
                        <p class="text-sm text-gray-600 mt-2">Datos Docente</p>
                    </div>
                    <div class="flex-1 text-center">
                        <div id="step-indicator-2" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-gray-300 text-gray-600 font-bold">2</div>
                        <p class="text-sm text-gray-600 mt-2">Marco Curricular</p>
                    </div>
                    <div class="flex-1 text-center">
                        <div id="step-indicator-3" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-gray-300 text-gray-600 font-bold">3</div>
                        <p class="text-sm text-gray-600 mt-2">Sugerencias</p>
                    </div>
                    <div class="flex-1 text-center">
                        <div id="step-indicator-4" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-gray-300 text-gray-600 font-bold">4</div>
                        <p class="text-sm text-gray-600 mt-2">Inclusión</p>
                    </div>
                    <div class="flex-1 text-center">
                        <div id="step-indicator-5" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-gray-300 text-gray-600 font-bold">5</div>
                        <p class="text-sm text-gray-600 mt-2">Info. Específica</p>
                    </div>
                    <div class="flex-1 text-center">
                        <div id="step-indicator-6" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-gray-300 text-gray-600 font-bold">6</div>
                        <p class="text-sm text-gray-600 mt-2">Sesiones</p>
                    </div>
                    <div class="flex-1 text-center">
                        <div id="step-indicator-7" class="w-10 h-10 mx-auto rounded-full text-lg flex items-center justify-center bg-gray-300 text-gray-600 font-bold">7</div>
                        <p class="text-sm text-gray-600 mt-2">Resumen</p>
                    </div>
                </div>

                <!-- Paso 1: Datos del Docente -->
                <div id="step-1" class="form-step">
                    <fieldset class="border p-4 rounded-md">
                        <legend class="text-lg font-semibold px-2">
                            <i class="fas fa-user-edit mr-2"></i> 1. Ingrese sus datos personales y de la escuela
                            <p class="text-sm text-gray-500 mt-1">Información de tu perfil, pre-cargada.</p>
                        </legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <label for="docente_nombre_completo" class="label-style">Nombre Completo del Docente</label>
                                <input type="text" name="docente_nombre_completo" id="docente_nombre_completo"
                                    value="<?= htmlspecialchars($user_data['nombre'] ?? '') ?> <?= htmlspecialchars($user_data['apellidos'] ?? '') ?>"
                                    class="input-style bg-gray-100" readonly>
                            </div>
                            <div>
                                <label for="docente_pais" class="label-style">País del Docente</label>
                                <input type="text" name="docente_pais" id="docente_pais"
                                    value="<?= htmlspecialchars($user_data['nombre_pais'] ?? 'No especificado') ?>"
                                    class="input-style bg-gray-100" readonly>
                                <input type="hidden" name="docente_pais_id" value="<?= htmlspecialchars($user_data['id_pais'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="escuela" class="label-style">Nombre de la Escuela</label>
                                <input type="text" name="escuela" id="escuela" placeholder="Ej: Escuela Primaria 'Benito Juárez'" class="input-style" required>
                            </div>
                            <div>
                                <label for="cct" class="label-style">CCT (Clave de Centro de Trabajo)</label>
                                <input type="text" name="cct" id="cct" placeholder="Ej: 09DPR0001A" class="input-style">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="asignatura_docente_id" class="label-style">Asignatura que Enseñas</label>
                            <?php if (count($user_asignaturas_data) === 1): ?>
                                <input type="text" name="asignatura_docente_display" id="asignatura_docente_display" value="<?= htmlspecialchars($user_asignaturas_data[0]['nombre']) ?>" readonly class="input-style bg-gray-100 cursor-not-allowed">
                                <input type="hidden" name="docente_asignatura_id" value="<?= htmlspecialchars($user_asignaturas_data[0]['asignatura_id']) ?>">
                            <?php elseif (count($user_asignaturas_data) > 1): ?>
                                <select name="docente_asignatura_id" id="asignatura_docente_id" class="input-style" required>
                                    <option value="" disabled selected>Selecciona una asignatura</option>
                                    <?php foreach ($user_asignaturas_data as $asignatura): ?>
                                        <option value="<?= htmlspecialchars($asignatura['asignatura_id']) ?>"><?= htmlspecialchars($asignatura['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" value="No has configurado asignaturas en tu perfil." readonly class="input-style bg-gray-100 cursor-not-allowed">
                                <input type="hidden" name="docente_asignatura_id" value="">
                                <p class="text-sm text-red-500 mt-1">Por favor, configura tus asignaturas en <a href="/dashboard/profile" class="underline">Mi Perfil</a> para poder continuar.</p>
                            <?php endif; ?>
                        </div>
                    </fieldset>
                    <div class="flex justify-end mt-6">
                        <button type="button" class="btn-next-step px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- Paso 2: Marco Curricular -->
                <div id="step-2" class="form-step hidden">
                    <fieldset class="border p-4 rounded-md">
                        <legend class="text-lg font-semibold px-2">2. Defina la información académica</legend>
                        <p class="text-sm text-gray-600 mb-4">Selecciona el contexto educativo de tu planeación. Estos datos son cruciales para la IA.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <label for="pais_id" class="label-style">País del Currículo</label>
                                <select id="pais_id" name="pais_id" class="input-style" required>
                                    <option value="" disabled selected>Selecciona un país</option>
                                    <?php foreach ($paises as $pais): ?>
                                        <option value="<?= htmlspecialchars($pais['id']) ?>"
                                            <?= (isset($user_data['id_pais']) && $user_data['id_pais'] == $pais['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pais['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="centro_educativo_id" class="label-style">Centro de Estudio</label>
                                <select id="centro_educativo_id" name="centro_educativo_id" class="input-style" disabled required>
                                    <option value="" disabled selected>Primero selecciona un país</option>
                                </select>
                            </div>
                            <div>
                                <label for="plan_estudio_id" class="label-style">Plan de Estudio</label>
                                <select id="plan_estudio_id" name="plan_estudio_id" class="input-style" disabled required>
                                    <option value="" disabled selected>Primero selecciona un centro educativo</option>
                                </select>
                            </div>
                            <div>
                                <label for="curso_id" class="label-style">Curso (Nivel, Grado, Asignatura)</label>
                                <select id="curso_id" name="curso_id" class="input-style" disabled required>
                                    <option value="" disabled selected>Primero selecciona un plan de estudio</option>
                                </select>
                                <input type="hidden" name="fase" id="fase">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="contenido_pda_id" class="label-style">Contenido Curricular</label>
                            <select id="contenido_pda_id" name="contenido_pda_id" class="input-style" disabled required>
                                <option value="" disabled selected>Selecciona un curso para cargar contenidos</option>
                            </option>
                        </div>
                        <div id="pda_display_container" class="hidden mt-4 p-3 bg-gray-50 rounded-md border">
                            <label class="label-style">Proceso de Desarrollo de Aprendizaje (PDA)</label>
                            <p id="pda_display" class="text-sm text-gray-700"></p>
                        </div>
                        <div class="mt-4">
                            <label class="label-style">Ejes Articuladores</label>
                            <div id="ejes_articuladores_container" class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2 p-3 bg-gray-50 rounded-md border">
                                <p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>
                            </div>
                        </div>
                    </fieldset>
                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn-prev-step px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors"><i class="fas fa-arrow-left mr-2"></i> Anterior</button>
                        <button type="button" class="btn-next-step px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- Paso 3: Sugerencias para las Sesiones -->
                <div id="step-3" class="form-step hidden">
                    <fieldset class="border p-4 rounded-md">
                        <legend class="text-lg font-semibold px-2">3. ¿Quiere añadir sugerencias para las sesiones?</legend>
                        <p class="text-sm text-gray-600 mb-4">Este campo es opcional. Cualquier detalle adicional que consideres relevante para la IA.</p>
                        <div class="mt-2">
                            <label for="sugerencias_sesiones" class="label-style">Sugerencias para las Sesiones (Opcional)</label>
                            <textarea name="sugerencias_sesiones" id="sugerencias_sesiones" rows="3" class="input-style" placeholder="Ej: Enfocarse en actividades al aire libre, incluir música en el inicio, etc."></textarea>
                        </div>
                        <div class="mt-4">
                            <label for="materiales" class="label-style">Materiales Disponibles</label>
                            <textarea name="materiales" id="materiales" rows="2" class="input-style" placeholder="Ej: Conos, aros, pelotas, cuerdas..."></textarea>
                        </div>
                    </fieldset>
                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn-prev-step px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors"><i class="fas fa-arrow-left mr-2"></i> Anterior</button>
                        <button type="button" class="btn-next-step px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- Paso 4: Inclusión y Grupos -->
                <div id="step-4" class="form-step hidden">
                    <fieldset class="border p-4 rounded-md">
                        <legend class="text-lg font-semibold px-2">4. ¿Desea indicar uno o varios de sus grupos?</legend>
                        <p class="text-sm text-gray-600 mb-4">Asocia un grupo existente para que la IA considere las necesidades de inclusión de tus estudiantes.</p>
                        <div class="mt-2">
                            <label for="grupo_id" class="label-style">Seleccionar Grupo (Opcional)</label>
                            <select id="grupo_id" name="grupo_id" class="input-style">
                                <option value="" selected>Sin grupo específico</option>
                                <!-- Los grupos del docente se cargarán aquí dinámicamente -->
                            </select>
                        </div>
                        <div id="alumnos_nee_container" class="mt-4 hidden">
                            <label for="alumnos_nee" class="label-style">Alumnos con Necesidades Educativas Especiales (NEE) en este grupo</label>
                            <textarea name="alumnos_nee" id="alumnos_nee" rows="4" class="input-style" placeholder="Ej: Juan - TDAH (requiere instrucciones claras y cortas). Ana - Hipoacusia (necesita apoyos visuales)." readonly></textarea>
                            <p class="text-xs text-gray-500 mt-1">Estas observaciones se cargan automáticamente del grupo seleccionado.</p>
                        </div>
                        <p class="text-sm text-gray-600 mt-4">
                            <a href="/dashboard/grupos" id="manage_groups_link" class="text-indigo-600 hover:text-indigo-800 font-semibold">Gestionar mis grupos y estudiantes</a>
                        </p>
                    </fieldset>
                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn-prev-step px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors"><i class="fas fa-arrow-left mr-2"></i> Anterior</button>
                        <button type="button" class="btn-next-step px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- Paso 5: Información Específica (Formulario Dinámico) -->
                <div id="step-5" class="form-step hidden">
                    <fieldset class="border p-4 rounded-md" id="dynamic_form_fieldset">
                        <legend class="text-lg font-semibold px-2">5. Otra información necesaria para la planeación</legend>
                        <p class="text-sm text-gray-600 mb-4">Estos campos son específicos para el curso seleccionado y ayudarán a la IA a generar una planeación más precisa.</p>
                        <div id="dynamic_form_container" class="space-y-4 mt-2">
                            <p class="text-gray-500 text-center">Selecciona un curso para cargar información específica.</p>
                        </div>
                        <input type="hidden" name="dynamic_form_data_json" id="dynamic_form_data_json">
                    </fieldset>
                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn-prev-step px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors"><i class="fas fa-arrow-left mr-2"></i> Anterior</button>
                        <button type="button" class="btn-next-step px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- Paso 6: Detalles de la Sesión -->
                <div id="step-6" class="form-step hidden">
                    <fieldset class="border p-4 rounded-md">
                        <legend class="text-lg font-semibold px-2">6. Sesiones a planear</legend>
                        <p class="text-sm text-gray-600 mb-4">Define la cantidad y duración de las sesiones a planear.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <label for="num_sesiones" class="label-style">Número de Sesiones</label>
                                <input type="number" name="num_sesiones" id="num_sesiones" value="1" min="1" max="10" class="input-style" required>
                            </div>
                            <div>
                                <label for="duracion_sesion" class="label-style">Duración por Sesión (min)</label>
                                <input type="number" name="duracion_sesion" id="duracion_sesion" value="50" min="20" max="60" step="5" class="input-style" required>
                            </div>
                        </div>
                    </fieldset>
                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn-prev-step px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors"><i class="fas fa-arrow-left mr-2"></i> Anterior</button>
                        <button type="button" class="btn-next-step px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <!-- Paso 7: Resumen y Confirmación -->
                <div id="step-7" class="form-step hidden">
                    <fieldset class="border p-4 rounded-md">
                        <legend class="text-lg font-semibold px-2">7. Resumen y Confirmación</legend>
                        <p class="text-sm text-gray-600 mb-4">Revisa los datos ingresados antes de generar tu planeación.</p>
                        <div id="summary_container" class="space-y-3 p-4 bg-gray-50 rounded-md border mb-6">
                            <!-- El resumen se generará aquí dinámicamente -->
                            <p class="text-gray-500 text-center">No hay datos para mostrar. Por favor, completa los pasos anteriores.</p>
                        </div>
                        <div class="flex items-center mb-4">
                            <input type="checkbox" name="confirm_generate" id="confirm_generate" required class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="confirm_generate" class="ml-2 text-sm text-gray-700">Confirmo que los datos ingresados son correctos y deseo generar la planeación.</label>
                        </div>
                        <p class="text-sm text-red-600 font-semibold mb-6">
                            Al generar la planeación, se descontará 1 crédito de tu cuenta. Créditos actuales: <?= htmlspecialchars($_SESSION['user_credits'] ?? 0) ?>.
                        </p>
                    </fieldset>
                    <div class="flex justify-between mt-6">
                        <button type="button" class="btn-prev-step px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors"><i class="fas fa-arrow-left mr-2"></i> Anterior</button>
                        <button type="submit" id="generate-btn" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                            <i class="fas fa-magic mr-2"></i>
                            Generar Planeación
                        </button>
                    </div>
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
            <!-- Controles post-generación -->
            <div id="post-generation-actions" class="p-4 border-t bg-gray-50 hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Acciones</h3>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="/dashboard" class="px-6 py-3 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
                    </a>
                    <button type="button" id="export-pdf-btn" class="px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i> Exportar a PDF
                    </button>
                    <button type="button" id="share-plan-btn" class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-share-alt mr-2"></i> Compartir Planeación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    .form-step {
        transition: opacity 0.3s ease-in-out;
    }
</style>
<script src="/js/main.js"></script>
EOF_PHP_CREATE_VIEW

# public/js/main.js
echo_color "1;32" "-> Creando/Reemplazando public/js/main.js..."
rm -f public/js/main.js # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_JS_MAIN' > public/js/main.js
// public/js/main.js (Fase 4)

document.addEventListener('DOMContentLoaded', function() {
    const planeacionForm = document.getElementById('planeacion-form');
    if (!planeacionForm) return; // Salir si no es la página de creación de planeación

    const steps = document.querySelectorAll('.form-step');
    const stepIndicators = document.querySelectorAll('[id^="step-indicator-"]');
    let currentStep = 0;

    const generateBtn = document.getElementById('generate-btn');
    const loader = document.getElementById('loader');
    const responseContainer = document.getElementById('ai-response-container');
    const postGenerationActions = document.getElementById('post-generation-actions');
    const exportPdfBtn = document.getElementById('export-pdf-btn');
    const sharePlanBtn = document.getElementById('share-plan-btn');

    // Selects dinámicos
    const paisSelect = document.getElementById('pais_id');
    const centroEducativoSelect = document.getElementById('centro_educativo_id');
    const planEstudioSelect = document.getElementById('plan_estudio_id');
    const cursoSelect = document.getElementById('curso_id');
    const contenidoPdaSelect = document.getElementById('contenido_pda_id');
    const pdaDisplayContainer = document.getElementById('pda_display_container');
    const pdaDisplay = document.getElementById('pda_display');
    const faseInput = document.getElementById('fase');
    const ejesArticuladoresContainer = document.getElementById('ejes_articuladores_container');
    const grupoSelect = document.getElementById('grupo_id');
    const alumnosNeeContainer = document.getElementById('alumnos_nee_container');
    const alumnosNeeTextarea = document.getElementById('alumnos_nee');

    const dynamicFormFieldsContainer = document.getElementById('dynamic_form_container');
    const dynamicFormFieldsFieldset = document.getElementById('dynamic_form_fieldset');
    const dynamicFormDataJsonInput = document.getElementById('dynamic_form_data_json');

    // Datos del docente pre-llenados
    const docenteNombreCompleto = document.getElementById('docente_nombre_completo');
    const docentePais = document.getElementById('docente_pais');
    const docentePaisId = document.querySelector('input[name="docente_pais_id"]');
    const docenteAsignaturaSelect = document.getElementById('asignatura_docente_id'); // Puede ser select o hidden input

    // Resumen final
    const summaryContainer = document.getElementById('summary_container');
    const confirmGenerateCheckbox = document.getElementById('confirm_generate');

    // --- Funciones de Utilidad ---

    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.classList.toggle('hidden', index !== stepIndex);
            stepIndicators[index].classList.remove('bg-indigo-600', 'text-white', 'bg-gray-300', 'text-gray-600', 'bg-green-500');
            if (index === stepIndex) {
                stepIndicators[index].classList.add('bg-indigo-600', 'text-white');
            } else if (index < stepIndex) {
                stepIndicators[index].classList.add('bg-green-500', 'text-white'); // Paso completado
            } else {
                stepIndicators[index].classList.add('bg-gray-300', 'text-gray-600');
            }
        });
        currentStep = stepIndex;
    }

    function goToNextStep() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
            if (currentStep === 6) { // Si estamos en el paso de resumen
                updateSummary();
            }
        }
    }

    function goToPrevStep() {
        currentStep--;
        showStep(currentStep);
        if (currentStep === 6) { // Si volvemos al paso de resumen
            updateSummary();
        }
    }

    function validateStep(stepIndex) {
        let isValid = true;
        const currentFormStep = steps[stepIndex];
        const requiredInputs = currentFormStep.querySelectorAll('[required]');

        requiredInputs.forEach(input => {
            if (input.type === 'checkbox') {
                if (!input.checked) {
                    isValid = false;
                    input.classList.add('border-red-500'); // Añadir clase para visual
                } else {
                    input.classList.remove('border-red-500');
                }
            } else if (!input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500'); // Añadir clase para visual
            } else {
                input.classList.remove('border-red-500');
            }
        });

        // Validación específica para asignaturas del docente en el Paso 1
        if (stepIndex === 0) {
            if (docenteAsignaturaSelect.tagName === 'SELECT' && !docenteAsignaturaSelect.value) {
                alert('Por favor, selecciona una asignatura que enseñas en tu perfil.');
                isValid = false;
            } else if (docenteAsignaturaSelect.tagName === 'INPUT' && !docenteAsignaturaSelect.value) {
                // Si es un input readonly y está vacío, significa que no configuró asignaturas
                alert('Por favor, configura tus asignaturas en tu perfil para poder continuar.');
                isValid = false;
            }
        }

        // Validación específica para campos dinámicos si están visibles
        if (stepIndex === 4 && dynamicFormFieldsFieldset.style.display !== 'none') {
            const dynamicRequiredInputs = dynamicFormFieldsContainer.querySelectorAll('[required]');
            dynamicRequiredInputs.forEach(input => {
                if (input.type === 'checkbox') {
                    if (!input.checked) {
                        isValid = false;
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                } else if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });
        }
        
        // Validación del checkbox de confirmación en el último paso
        if (stepIndex === 6) {
            const confirmCheckbox = document.getElementById('confirm_generate');
            if (!confirmCheckbox.checked) {
                alert('Debes confirmar los datos para generar la planeación.');
                isValid = false;
            }
        }


        if (!isValid) {
            // No alertamos aquí, la validación del navegador y el mensaje de alert específico ya lo hacen.
        }
        return isValid;
    }

    // --- Lógica de Carga Dinámica de Selects (existente, adaptada) ---
    paisSelect.addEventListener('change', async function() {
        const paisId = this.value;
        resetAndDisableSelect(centroEducativoSelect, 'Cargando centros educativos...');
        resetAndDisableSelect(planEstudioSelect, 'Primero selecciona un centro educativo');
        resetAndDisableSelect(cursoSelect, 'Primero selecciona un plan de estudio');
        resetAndDisableSelect(contenidoPdaSelect, 'Selecciona un curso para cargar contenidos');
        pdaDisplayContainer.classList.add('hidden');
        faseInput.value = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        grupoSelect.innerHTML = '<option value="" selected>Sin grupo específico</option>';
        alumnosNeeContainer.classList.add('hidden');
        clearDynamicForm();

        if (paisId) {
            try {
                const response = await fetch(`/api/centros-educativos?pais_id=${paisId}`);
                const data = await response.json();
                centroEducativoSelect.innerHTML = '<option value="" disabled selected>Selecciona un centro educativo</option>';
                if (data.success && data.centros.length > 0) {
                    data.centros.forEach(centro => {
                        const option = document.createElement('option');
                        option.value = centro.id;
                        option.textContent = centro.nombre;
                        centroEducativoSelect.appendChild(option);
                    });
                    centroEducativoSelect.disabled = false;
                } else {
                    centroEducativoSelect.innerHTML = '<option value="" disabled selected>No hay centros para este país</option>';
                }
            } catch (error) {
                console.error('Error al cargar centros educativos:', error);
                centroEducativoSelect.innerHTML = '<option value="" disabled selected>Error al cargar centros</option>';
            }
        }
    });

    centroEducativoSelect.addEventListener('change', async function() {
        const centroId = this.value;
        resetAndDisableSelect(planEstudioSelect, 'Cargando planes de estudio...');
        resetAndDisableSelect(cursoSelect, 'Primero selecciona un plan de estudio');
        resetAndDisableSelect(contenidoPdaSelect, 'Selecciona un curso para cargar contenidos');
        pdaDisplayContainer.classList.add('hidden');
        faseInput.value = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        grupoSelect.innerHTML = '<option value="" selected>Sin grupo específico</option>';
        alumnosNeeContainer.classList.add('hidden');
        clearDynamicForm();

        if (centroId) {
            try {
                const response = await fetch(`/api/planes-estudio?centro_educativo_id=${centroId}`);
                const data = await response.json();
                planEstudioSelect.innerHTML = '<option value="" disabled selected>Selecciona un plan de estudio</option>';
                if (data.success && data.planes.length > 0) {
                    data.planes.forEach(plan => {
                        const option = document.createElement('option');
                        option.value = plan.id;
                        option.textContent = `${plan.nombre_plan} (${plan.anio_vigencia_inicio}${plan.anio_vigencia_fin ? '-' + plan.anio_vigencia_fin : ''})`;
                        planEstudioSelect.appendChild(option);
                    });
                    planEstudioSelect.disabled = false;
                } else {
                    planEstudioSelect.innerHTML = '<option value="" disabled selected>No hay planes para este centro</option>';
                }
            } catch (error) {
                console.error('Error al cargar planes de estudio:', error);
                planEstudioSelect.innerHTML = '<option value="" disabled selected>Error al cargar planes</option>';
            }
        }
    });

    planEstudioSelect.addEventListener('change', async function() {
        const planId = this.value;
        resetAndDisableSelect(cursoSelect, 'Cargando cursos...');
        resetAndDisableSelect(contenidoPdaSelect, 'Selecciona un curso para cargar contenidos');
        pdaDisplayContainer.classList.add('hidden');
        faseInput.value = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        grupoSelect.innerHTML = '<option value="" selected>Sin grupo específico</option>';
        alumnosNeeContainer.classList.add('hidden');
        clearDynamicForm();

        if (planId) {
            try {
                const response = await fetch(`/api/cursos?plan_estudio_id=${planId}`);
                const data = await response.json();
                cursoSelect.innerHTML = '<option value="" disabled selected>Selecciona un curso</option>';
                if (data.success && data.cursos.length > 0) {
                    data.cursos.forEach(curso => {
                        const option = document.createElement('option');
                        option.value = curso.id;
                        option.textContent = `${curso.nivel} - ${curso.grado} - ${curso.asignatura}`;
                        option.dataset.fase = curso.fase || ''; // Guardar la fase
                        cursoSelect.appendChild(option);
                    });
                    cursoSelect.disabled = false;
                } else {
                    cursoSelect.innerHTML = '<option value="" disabled selected>No hay cursos para este plan</option>';
                }
            } catch (error) {
                console.error('Error al cargar cursos:', error);
                cursoSelect.innerHTML = '<option value="" disabled selected>Error al cargar cursos</option>';
            }
        }
    });

    cursoSelect.addEventListener('change', async function() {
        const cursoId = this.value;
        faseInput.value = this.options[this.selectedIndex].dataset.fase || '';
        resetAndDisableSelect(contenidoPdaSelect, 'Cargando contenidos y ejes...');
        pdaDisplayContainer.classList.add('hidden');
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        grupoSelect.innerHTML = '<option value="" selected>Sin grupo específico</option>';
        alumnosNeeContainer.classList.add('hidden');
        clearDynamicForm();

        if (cursoId) {
            try {
                // Cargar Contenidos Curriculares y PDAs
                const contenidosResponse = await fetch(`/api/contenidos-curriculares?curso_id=${cursoId}&tipo=contenido`);
                const contenidosData = await contenidosResponse.json();
                contenidoPdaSelect.innerHTML = '<option value="" disabled selected>Selecciona un contenido</option>';
                if (contenidosData.success && contenidosData.contenidos.length > 0) {
                    contenidosData.contenidos.forEach(item => {
                        const option = document.createElement('option');
                        option.value = `${item.nombre_contenido}||${item.pda_descripcion}`;
                        option.textContent = item.nombre_contenido;
                        contenidoPdaSelect.appendChild(option);
                    });
                    contenidoPdaSelect.disabled = false;
                } else {
                    contenidoPdaSelect.innerHTML = '<option value="" disabled selected>No hay contenidos para este curso</option>';
                }

                // Cargar Ejes Articuladores
                const ejesResponse = await fetch(`/api/contenidos-curriculares?curso_id=${cursoId}&tipo=eje_articulador`);
                const ejesData = await ejesResponse.json();
                if (ejesData.success && ejesData.contenidos.length > 0) {
                    ejesArticuladoresContainer.innerHTML = ''; // Asegurarse de limpiar antes de añadir
                    ejesData.contenidos.forEach(eje => {
                        const div = document.createElement('div');
                        div.innerHTML = `
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="ejes_articuladores[]" value="${eje.nombre_contenido}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">${eje.nombre_contenido}</span>
                            </label>
                        `;
                        ejesArticuladoresContainer.appendChild(div);
                    });
                } else {
                    ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500">No hay ejes articuladores definidos para este curso.</p>';
                }

                // Cargar Grupos del docente para este curso
                const gruposResponse = await fetch(`/api/grupos?curso_id=${cursoId}`);
                const gruposData = await gruposResponse.json();
                grupoSelect.innerHTML = '<option value="" selected>Sin grupo específico</option>';
                if (gruposData.success && gruposData.grupos.length > 0) {
                    gruposData.grupos.forEach(grupo => {
                        const option = document.createElement('option');
                        option.value = grupo.id;
                        option.textContent = grupo.nombre_grupo;
                        grupoSelect.appendChild(option);
                    });
                } else {
                    grupoSelect.innerHTML = '<option value="" selected>No tienes grupos creados para este curso.</option>';
                }

                // Cargar Formulario Dinámico
                const formResponse = await fetch(`/api/formulario-dinamico-by-curso?curso_id=${cursoId}`);
                const formData = await formResponse.json();
                if (formData.success && formData.formulario && formData.formulario.schema_json && formData.formulario.schema_json.fields) {
                    renderDynamicForm(formData.formulario.schema_json.fields);
                    dynamicFormFieldsFieldset.style.display = 'block'; // Mostrar el fieldset
                } else {
                    clearDynamicForm(); // Ocultar si no hay formulario
                }

            } catch (error) {
                console.error('Error al cargar datos del curso:', error);
                contenidoPdaSelect.innerHTML = '<option value="" disabled selected>Error al cargar contenidos</option>';
                ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-red-500">Error al cargar ejes articuladores.</p>';
                grupoSelect.innerHTML = '<option value="" selected>Error al cargar grupos.</option>';
                clearDynamicForm(); // Ocultar formulario dinámico en caso de error
            }
        }
    });

    contenidoPdaSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        const parts = selectedValue.split('||');
        if (parts.length > 1) {
            pdaDisplay.textContent = parts[1];
            pdaDisplayContainer.classList.remove('hidden');
        } else {
            pdaDisplay.textContent = '';
            pdaDisplayContainer.classList.add('hidden');
        }
    });

    grupoSelect.addEventListener('change', async function() {
        const grupoId = this.value;
        alumnosNeeTextarea.value = '';
        alumnosNeeContainer.classList.add('hidden');

        if (grupoId) {
            try {
                const response = await fetch(`/api/grupo-estudiantes?grupo_id=${grupoId}`);
                const data = await response.json();
                if (data.success && data.estudiantes.length > 0) {
                    let neeNotes = '';
                    data.estudiantes.forEach(estudiante => {
                        if (estudiante.observaciones_inclusion) {
                            neeNotes += `${estudiante.nombre_estudiante}: ${estudiante.observaciones_inclusion}\n`;
                        }
                    });
                    if (neeNotes) {
                        alumnosNeeTextarea.value = neeNotes.trim();
                        alumnosNeeContainer.classList.remove('hidden');
                    } else {
                        alumnosNeeTextarea.value = 'No hay observaciones de inclusión específicas para los estudiantes de este grupo.';
                        alumnosNeeContainer.classList.remove('hidden');
                    }
                } else {
                    alumnosNeeTextarea.value = 'No hay estudiantes asociados a este grupo o no tienen observaciones de inclusión.';
                    alumnosNeeContainer.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error al cargar estudiantes del grupo:', error);
                alumnosNeeTextarea.value = 'Error al cargar las observaciones de inclusión.';
                alumnosNeeContainer.classList.add('hidden');
            }
        }
    });

    // --- Lógica para Renderizar Formulario Dinámico ---
    function renderDynamicForm(fields) {
        dynamicFormFieldsContainer.innerHTML = ''; // Limpiar campos existentes
        if (!fields || fields.length === 0) {
            dynamicFormFieldsFieldset.style.display = 'none';
            return;
        }
        dynamicFormFieldsFieldset.style.display = 'block'; // Mostrar el fieldset

        fields.forEach(field => {
            const fieldWrapper = document.createElement('div');
            fieldWrapper.className = 'mb-4';

            const label = document.createElement('label');
            label.htmlFor = `dynamic_field_${field.name}`;
            label.className = 'label-style';
            label.textContent = field.label;
            if (field.required) {
                label.textContent += ' *'; // Indicador de campo obligatorio
            }
            fieldWrapper.appendChild(label);

            let inputElement;
            switch (field.type) {
                case 'text':
                case 'number':
                    inputElement = document.createElement('input');
                    inputElement.type = field.type;
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`; // Prefijo para identificar campos dinámicos
                    inputElement.className = 'input-style';
                    if (field.required) inputElement.required = true;
                    if (field.type === 'number') {
                        inputElement.step = 'any'; // Permite decimales
                    }
                    break;
                case 'textarea':
                    inputElement = document.createElement('textarea');
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'input-style';
                    inputElement.rows = 3;
                    if (field.required) inputElement.required = true;
                    break;
                case 'checkbox':
                    inputElement = document.createElement('input');
                    inputElement.type = 'checkbox';
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50';
                    const checkboxLabel = document.createElement('label');
                    checkboxLabel.htmlFor = `dynamic_field_${field.name}`;
                    checkboxLabel.className = 'ml-2 text-sm text-gray-700 inline-flex items-center';
                    checkboxLabel.textContent = field.label; 
                    
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.className = 'flex items-center';
                    checkboxDiv.appendChild(inputElement);
                    checkboxDiv.appendChild(checkboxLabel);
                    fieldWrapper.removeChild(label); 
                    fieldWrapper.appendChild(checkboxDiv);
                    break;
                case 'select':
                    inputElement = document.createElement('select');
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'input-style';
                    if (field.required) inputElement.required = true;
                    
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = `Selecciona ${field.label.toLowerCase()}`;
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    inputElement.appendChild(defaultOption);

                    if (field.options && Array.isArray(field.options)) {
                        field.options.forEach(optionText => {
                            const option = document.createElement('option');
                            option.value = optionText;
                            option.textContent = optionText;
                            inputElement.appendChild(option);
                        });
                    }
                    break;
                default:
                    console.warn(`Tipo de campo desconocido: ${field.type}`);
                    return;
            }

            if (field.type !== 'checkbox') {
                fieldWrapper.appendChild(inputElement);
            }
            dynamicFormFieldsContainer.appendChild(fieldWrapper);
        });
    }


    // --- Lógica de Envío del Formulario ---
    planeacionForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validar el último paso (Resumen y Confirmación)
        if (!validateStep(currentStep)) {
            return;
        }

        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generando...';
        loader.classList.remove('hidden');
        responseContainer.innerHTML = '';
        postGenerationActions.classList.add('hidden'); // Ocultar acciones post-generación

        const formData = new FormData(planeacionForm);

        // Recopilar datos de los campos dinámicos y añadirlos al formData
        const dynamicFieldsData = {};
        dynamicFormFieldsContainer.querySelectorAll('input, textarea, select').forEach(input => {
            if (input.name.startsWith('dynamic_field_')) {
                const originalName = input.name.replace('dynamic_field_', '');
                if (input.type === 'checkbox') {
                    dynamicFieldsData[originalName] = input.checked;
                } else {
                    dynamicFieldsData[originalName] = input.value;
                }
            }
        });
        formData.append('dynamic_form_data_json', JSON.stringify(dynamicFieldsData));


        // Añadir el ID del curso y grupo al formData si están seleccionados
        const selectedCursoId = cursoSelect.value;
        if (selectedCursoId) {
            formData.append('id_curso', selectedCursoId);
        }
        const selectedGrupoId = grupoSelect.value;
        if (selectedGrupoId) {
            formData.append('id_grupo', selectedGrupoId);
        }

        // Añadir la asignatura del docente seleccionada (si es un select)
        const docenteAsignaturaIdInput = document.getElementById('asignatura_docente_id');
        if (docenteAsignaturaIdInput && docenteAsignaturaIdInput.tagName === 'SELECT' && docenteAsignaturaIdInput.value) {
            formData.append('docente_asignatura_id', docenteAsignaturaIdInput.value);
        }


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
                responseContainer.innerHTML = `<h3 class="text-xl font-bold mb-4 text-green-700">¡Planeación Generada con Éxito!</h3><div class="prose max-w-none">${result.data}</div>`;
                postGenerationActions.classList.remove('hidden');
                // Actualizar enlaces de descarga/compartir
                localStorage.setItem('last_generated_planeacion_id', result.planeacion_id); // Guardar ID para usar en otros botones
                
            } else {
                throw new Error(result.message || 'Ocurrió un error desconocido al generar la planeación.');
            }
        } catch (error) {
            responseContainer.innerHTML = `<p class="text-red-600 font-semibold">Error al generar: ${error.message}</p>`;
            postGenerationActions.classList.add('hidden');
        } finally {
            loader.classList.add('hidden');
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> Generar Planeación';
        }
    });

    // --- Función para actualizar el resumen del Paso 7 ---
    function updateSummary() {
        const summaryContainer = document.getElementById('summary_container');
        let summaryHtml = '<h4 class="font-bold text-gray-800 mb-2">Resumen de tu Planeación:</h4>';
        const formData = new FormData(planeacionForm);

        // Recopilar datos de todos los campos (incluyendo dinámicos)
        const allFormData = {};
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('dynamic_field_')) {
                const originalName = key.replace('dynamic_field_', '');
                // Manejar checkboxes booleanos
                const inputElement = document.getElementById(key);
                if (inputElement && inputElement.type === 'checkbox') {
                    allFormData[originalName] = inputElement.checked ? 'Sí' : 'No';
                } else {
                    allFormData[originalName] = value;
                }
            } else if (key === 'ejes_articuladores[]') {
                // Recopilar todos los checkboxes de ejes
                if (!allFormData['ejes_articuladores']) {
                    allFormData['ejes_articuladores'] = [];
                }
                allFormData['ejes_articuladores'].push(value);
            } else {
                allFormData[key] = value;
            }
        }

        // Datos del Docente y Escuela
        summaryHtml += `<p><span class="font-semibold">Docente:</span> ${allFormData['docente_nombre_completo'] || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">País Docente:</span> ${document.getElementById('docente_pais').value || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Escuela:</span> ${allFormData['escuela'] || 'N/A'} (CCT: ${allFormData['cct'] || 'N/A'})</p>`;
        summaryHtml += `<p><span class="font-semibold">Asignatura Docente:</span> ${document.getElementById('asignatura_docente_display')?.value || document.getElementById('asignatura_docente_id')?.options[document.getElementById('asignatura_docente_id').selectedIndex]?.text || 'N/A'}</p>`;

        // Marco Curricular
        summaryHtml += `<p class="mt-4"><span class="font-semibold">País Currículo:</span> ${paisSelect.options[paisSelect.selectedIndex]?.text || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Centro Educativo:</span> ${centroEducativoSelect.options[centroEducativoSelect.selectedIndex]?.text || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Plan de Estudio:</span> ${planEstudioSelect.options[planEstudioSelect.selectedIndex]?.text || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Curso:</span> ${cursoSelect.options[cursoSelect.selectedIndex]?.text || 'N/A'} (Fase: ${faseInput.value || 'N/A'})</p>`;
        summaryHtml += `<p><span class="font-semibold">Contenido Curricular:</span> ${contenidoPdaSelect.options[contenidoPdaSelect.selectedIndex]?.text || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">PDA:</span> ${pdaDisplay.textContent || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Ejes Articuladores:</span> ${allFormData['ejes_articuladores']?.join(', ') || 'No seleccionados'}</p>`;

        // Sugerencias y Materiales
        summaryHtml += `<p class="mt-4"><span class="font-semibold">Sugerencias:</span> ${allFormData['sugerencias_sesiones'] || 'Ninguna'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Materiales:</span> ${allFormData['materiales'] || 'No especificados'}</p>`;

        // Inclusión y Grupos
        summaryHtml += `<p class="mt-4"><span class="font-semibold">Grupo Asociado:</span> ${grupoSelect.options[grupoSelect.selectedIndex]?.text || 'Sin grupo específico'}</p>`;
        if (alumnosNeeTextarea.value.trim()) {
            summaryHtml += `<p><span class="font-semibold">Notas NEE:</span> <pre class="whitespace-pre-wrap text-sm">${alumnosNeeTextarea.value.trim()}</pre></p>`;
        } else {
            summaryHtml += `<p><span class="font-semibold">Notas NEE:</span> Ninguna</p>`;
        }

        // Información Específica (Formulario Dinámico)
        if (Object.keys(dynamicFieldsData).length > 0) {
            summaryHtml += `<p class="mt-4 font-semibold">Información Específica del Curso:</p>`;
            for (const key in dynamicFieldsData) {
                summaryHtml += `<p class="ml-4"><span class="font-semibold">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</span> ${dynamicFieldsData[key]}</p>`;
            }
        }

        // Detalles de Sesión
        summaryHtml += `<p class="mt-4"><span class="font-semibold">Número de Sesiones:</span> ${allFormData['num_sesiones'] || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Duración por Sesión:</span> ${allFormData['duracion_sesion'] || 'N/A'} minutos</p>`;

        summaryContainer.innerHTML = summaryHtml;
    }

    // Lógica para el botón de compartir (similar a la del dashboard)
    sharePlanBtn.addEventListener('click', function() {
        const planId = localStorage.getItem('last_generated_planeacion_id');
        if (!planId) {
            alert('No se pudo obtener el ID de la planeación para compartir.');
            return;
        }
        const shareUrl = window.location.origin + '/dashboard/view/' + planId; // URL de la planeación

        const emailSubject = encodeURIComponent('Planeación Didáctica de PlaneaIA');
        const emailBody = encodeURIComponent('Hola,\n\nTe comparto esta planeación didáctica generada con PlaneaIA:\n' + shareUrl + '\n\nSaludos.');
        const whatsappText = encodeURIComponent('Mira esta planeación didáctica que generé con PlaneaIA: ' + shareUrl);

        const shareOptions = `
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-8 rounded-lg shadow-xl w-96">
                    <h3 class="text-xl font-bold mb-4">Compartir Planeación</h3>
                    <p class="mb-4">Elige cómo quieres compartir esta planeación:</p>
                    <div class="space-y-3">
                        <a href="mailto:?subject=${emailSubject}&body=${emailBody}" target="_blank" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                            <i class="fas fa-envelope mr-2"></i> Compartir por Email
                        </a>
                        <a href="https://wa.me/?text=${whatsappText}" target="_blank" class="flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                            <i class="fab fa-whatsapp mr-2"></i> Compartir por WhatsApp
                        </a>
                        <button class="flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition-colors w-full" onclick="navigator.clipboard.writeText('${shareUrl}'); alert('Enlace copiado al portapapeles!'); this.closest('.fixed').remove();">
                            <i class="fas fa-copy mr-2"></i> Copiar Enlace
                        </button>
                        <button class="w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors mt-4" onclick="this.closest('.fixed').remove()">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', shareOptions);
    });

    exportPdfBtn.addEventListener('click', function() {
        const planId = localStorage.getItem('last_generated_planeacion_id');
        if (planId) {
            window.location.href = `/dashboard/pdf/${planId}`;
        } else {
            alert('No se encontró el ID de la planeación para exportar a PDF.');
        }
    });

});
EOF_JS_MAIN

echo_color "1;32" "   Archivos PHP y JS creados/reemplazados con éxito."

echo ""
echo_color "1;32" "¡Fase 4 completada!"
echo ""
echo_color "1;33" "--- Próximos Pasos ---"
echo_color "1;37" "1.  Inicia sesión como un docente."
echo_color "1;37" "2.  Navega a /dashboard/create."
echo_color "1;37" "3.  Prueba el formulario multi-paso, llenando todos los datos."
echo_color "1;37" "4.  Asegúrate de que los datos del perfil se pre-carguen."
echo_color "1;37" "5.  Prueba la selección de país, centro, plan, curso y que el formulario dinámico aparezca."
echo_color "1;37" "6.  Intenta generar una planeación y verifica el flujo de créditos y la visualización de la respuesta."
echo ""
echo_color "1;34" "======================================================"
echo_color "1;34" "==                 Fin del Script                 =="
echo_color "1;34" "======================================================"
echo ""

