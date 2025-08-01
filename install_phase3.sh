#!/bin/bash

# ==============================================================================
# Script de Instalación - Fase 3: Mejoras en el Dashboard del Docente
# ==============================================================================
# Este script actualiza el DashboardController y la vista principal del dashboard
# para la Fase 3 del proyecto PlaneaIA.
#
# USO:
# 1. Guarda este archivo como `install_phase3.sh` en el directorio raíz del proyecto.
# 2. Dale permisos de ejecución: chmod +x install_phase3.sh
# 3. Ejecútalo: ./install_phase3.sh
#

echo_color() {
    local color_code=$1
    shift
    echo -e "\033[${color_code}m$@\033[0m"
}

clear
echo_color "1;34" "======================================================"
echo_color "1;34" "==   Instalador PlaneaIA - Fase 3: Dashboard      =="
echo_color "1;34" "======================================================"
echo ""
echo_color "1;33" "Este script actualizará la vista principal del dashboard del docente."
echo_color "1;31" "¡ADVERTENCIA! Se eliminarán y recrearán los archivos modificados."
read -p "¿Estás listo para continuar? (s/n): " confirm
if [[ "$confirm" != "s" && "$confirm" != "S" ]]; then
    echo_color "1;31" "Instalación de Fase 3 cancelada."
    exit 1
fi

# --- Paso 1: Crear directorios necesarios (si aplica) ---
echo_color "1;32" "-> Creando directorios necesarios (si aplica)..."
# No se necesitan nuevos directorios específicos para esta fase, pero se mantiene la estructura
echo_color "1;32" "   Directorios verificados."

# --- Paso 2: Crear/Reemplazar archivos PHP ---

# app/controllers/DashboardController.php
echo_color "1;32" "-> Creando/Reemplazando app/controllers/DashboardController.php..."
rm -f app/controllers/DashboardController.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_DASHBOARD_CONTROLLER' > app/controllers/DashboardController.php
<?php
// Archivo: app/controllers/DashboardController.php (Fase 3)

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

# app/views/dashboard/index.php
echo_color "1;32" "-> Creando/Reemplazando app/views/dashboard/index.php..."
rm -f app/views/dashboard/index.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_DASHBOARD_INDEX' > app/views/dashboard/index.php
<?php
// app/views/dashboard/index.php (Fase 3)

<div class="container mx-auto py-10 px-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Docente') ?>!</h1>
            <p class="text-lg text-gray-600 font-roboto">Aquí tienes un resumen de tu actividad y cuenta.</p>
        </div>
        <a href="/dashboard/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nueva Planeación
        </a>
    </div>

    <!-- Sección: Estado Actual del Perfil y Créditos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Tarjeta: Estado del Perfil -->
        <div class="bg-white p-6 rounded-lg card-shadow flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-user-check mr-3 text-indigo-600"></i> Estado del Perfil
                </h2>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-3">
                    <div class="bg-indigo-600 h-4 rounded-full" style="width: <?= htmlspecialchars($profile_completion_percentage) ?>%;"></div>
                </div>
                <p class="text-lg text-gray-700 text-center">
                    Completado: <span class="font-semibold"><?= htmlspecialchars($profile_completion_percentage) ?>%</span>
                </p>
            </div>
            <div class="mt-4 text-center">
                <a href="/dashboard/profile" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">
                    <i class="fas fa-arrow-right mr-1"></i> Ir a Mi Perfil
                </a>
            </div>
        </div>

        <!-- Tarjeta: Mis Créditos -->
        <div class="bg-white p-6 rounded-lg card-shadow flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-coins mr-3 text-yellow-600"></i> Mis Créditos
                </h2>
                <p class="text-5xl font-extrabold text-center text-yellow-700">
                    <?= htmlspecialchars($user_data['creditos'] ?? 0) ?>
                </p>
                <p class="text-lg text-gray-700 text-center">
                    Créditos disponibles
                </p>
            </div>
            <div class="mt-4 text-center">
                <a href="#" class="text-yellow-600 hover:text-yellow-800 font-semibold text-sm">
                    <i class="fas fa-plus-circle mr-1"></i> Cargar Créditos (Próximamente)
                </a>
            </div>
        </div>

        <!-- Tarjeta: Precargar mis grupos y estudiantes -->
        <div class="bg-white p-6 rounded-lg card-shadow flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-users mr-3 text-teal-600"></i> Mis Grupos y Estudiantes
                </h2>
                <p class="text-lg text-gray-700">
                    Gestiona tus grupos de alumnos y sus necesidades de inclusión para una planificación más precisa.
                </p>
            </div>
            <div class="mt-4 text-center">
                <a href="/dashboard/grupos" class="text-teal-600 hover:text-teal-800 font-semibold text-sm">
                    <i class="fas fa-arrow-right mr-1"></i> Ir a Grupos
                </a>
            </div>
        </div>
    </div>

    <!-- Sección: Mis Planeaciones -->
    <div class="bg-white p-8 rounded-lg card-shadow">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center section-header">
            <i class="fas fa-clipboard-list mr-3 text-blue-600"></i> Mis Planeaciones
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">Contenido Principal</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nivel y Grado</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Fecha</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
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
                            <?php
                                $prompt_data = json_decode($plan['prompt_data'], true);
                                $contenido_pda = $prompt_data['contenido_pda_id'] ?? '||'; // Ahora viene como ID del contenido
                                $contenido_display = trim(explode('||', $contenido_pda)[0]); // Extraer solo el nombre
                            ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($plan['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($contenido_display ?: 'Sin Contenido') ?></td>
                                <td class="py-3 px-4">
                                    <?= htmlspecialchars($plan['nivel'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($plan['grado'] ?? 'N/A') ?>
                                    (<?= htmlspecialchars($plan['asignatura'] ?? 'N/A') ?>)
                                </td>
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($plan['fecha_creacion'])) ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/dashboard/view/<?= htmlspecialchars($plan['id']) ?>" class="text-indigo-600 hover:text-indigo-900 font-semibold" title="Ver Detalle">Ver</a>
                                        <a href="/dashboard/pdf/<?= htmlspecialchars($plan['id']) ?>" class="text-red-500 hover:text-red-700" title="Descargar PDF">
                                            <i class="fas fa-file-pdf fa-lg"></i>
                                        </a>
                                        <button type="button" class="text-green-500 hover:text-green-700 share-btn" data-plan-id="<?= htmlspecialchars($plan['id']) ?>" title="Compartir Planeación">
                                            <i class="fas fa-share-alt fa-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Lógica para el botón de compartir (ejemplo básico)
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', function() {
            const planId = this.dataset.planId;
            const shareUrl = window.location.origin + '/dashboard/view/' + planId; // URL de la planeación

            // Opciones de compartir
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
    });
</script>
EOF_PHP_DASHBOARD_INDEX

echo_color "1;32" "   Archivos PHP y JS creados/reemplazados con éxito."

echo ""
echo_color "1;32" "¡Fase 3 completada!"
echo ""
echo_color "1;33" "--- Próximos Pasos ---"
echo_color "1;37" "1.  Inicia sesión como un docente."
echo_color "1;37" "2.  Navega a /dashboard para ver las nuevas secciones."
echo_color "1;37" "3.  Verifica que el porcentaje del perfil se muestre correctamente."
echo_color "1;37" "4.  Asegúrate de que los enlaces a 'Mi Perfil' y 'Mis Grupos' funcionen."
echo_color "1;37" "5.  Ahora puedes proceder con la Fase 4 de la implementación."
echo ""
echo_color "1;34" "======================================================"
echo_color "1;34" "==                 Fin del Script                 =="
echo_color "1;34" "======================================================"
echo ""

