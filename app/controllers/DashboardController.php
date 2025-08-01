<?php
// Archivo: app/controllers/DashboardController.php (Actualizado con Debugging para lista)

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
        
        // Obtener planeaciones con detalles del curso para la lista
        $planeaciones = $this->planeacionModel->getByUserIdWithCourseDetails($user_id);
        
        // --- Obtener nombres de Centros Educativos para las planeaciones ---
        $all_centros_educativos = $this->centroEducativoModel->getAll(null, true); // Obtener todos los centros activos
        $centros_map = [];
        foreach ($all_centros_educativos as $centro) {
            $centros_map[$centro['id']] = $centro['nombre'];
        }
        error_log("DEBUG: Centros Map (ID => Nombre): " . print_r($centros_map, true)); // DEBUG LOG: Muestra el mapa de centros

        // Iterar a través de las planeaciones para adjuntar el nombre del centro educativo y decodificar prompt_data
        foreach ($planeaciones as &$planeacion) { // Usar & para modificar el array directamente
            $prompt_data_decoded = json_decode($planeacion['prompt_data'], true);
            $planeacion['prompt_data_decoded'] = $prompt_data_decoded; // Almacenar los datos decodificados

            // --- Debugging para Centro Educativo ---
            $centro_id_from_prompt = $prompt_data_decoded['centro_educativo_id'] ?? null;
            $planeacion['centro_educativo_nombre'] = $centros_map[$centro_id_from_prompt] ?? 'N/A';
            error_log("DEBUG: Planeacion ID: {$planeacion['id']}, Centro ID del prompt: " . ($centro_id_from_prompt ?? 'NULL') . ", Nombre de Centro resuelto: {$planeacion['centro_educativo_nombre']}");

            // --- Debugging para PDA ---
            $contenido_pda_full = $prompt_data_decoded['contenido_pda_id'] ?? '||';
            $contenido_pda_parts = explode('||', $contenido_pda_full);
            $pda_display_debug = trim($contenido_pda_parts[1] ?? 'N/A');
            error_log("DEBUG: Planeacion ID: {$planeacion['id']}, Contenido_PDA_ID completo: '{$contenido_pda_full}', PDA extraído: '{$pda_display_debug}'");

            // --- Debugging para Eje Articulador ---
            $ejes_articuladores_array = $prompt_data_decoded['ejes_articuladores'] ?? [];
            $ejes_display_debug = !empty($ejes_articuladores_array) ? implode(', ', $ejes_articuladores_array) : 'N/A';
            error_log("DEBUG: Planeacion ID: {$planeacion['id']}, Ejes Articuladores (array): " . print_r($ejes_articuladores_array, true) . ", Ejes extraídos: '{$ejes_display_debug}'");
        }
        unset($planeacion); // Romper la referencia al último elemento

        $grupos = $this->grupoModel->getByDocenteId($user_id);

        // --- Calcular porcentaje de perfil completado ---
        $profile_completion_percentage = 0;
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
        // Los cursos se cargarán dinámicamente en el frontend después de seleccionar plan de estudio.

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

        // Obtener la planeación con los detalles del curso ya unidos
        $planeacion = $this->planeacionModel->getByIdAndUserId($id, $_SESSION['user_id']);

        if (!$planeacion) {
            header('Location: /dashboard');
            exit;
        }

        // Decodificar los datos del prompt para acceder a los campos individuales
        $prompt_data_decoded = json_decode($planeacion['prompt_data'], true);

        // Pasar la planeación (con curso details) y los datos del prompt decodificados a la vista
        $data = [
            'planeacion' => $planeacion,
            'prompt_data_decoded' => $prompt_data_decoded,
            // Los detalles del curso ya vienen en $planeacion gracias al JOIN en el modelo
        ];

        // Pasar los datos a la vista
        extract($data); // Esto hace que $planeacion y $prompt_data_decoded estén disponibles en la vista

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
        // Obtener la planeación con los detalles del curso ya unidos
        $planeacion = $this->planeacionModel->getByIdAndUserId($id, $_SESSION['user_id']);

        if (!$planeacion) {
            header('Location: /dashboard');
            exit;
        }

        require_once '../app/controllers/PdfController.php';
        $pdfController = new PdfController();
        // El método generatePdf de PdfController ya no necesita los detalles del curso por separado
        // porque la respuesta_ia ya es HTML completo y la planeacion ya tiene los datos del curso si se necesitan.
        $pdfController->generatePdf($id); // Pasamos solo el ID, el controlador de PDF lo buscará
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

        $password_to_update = !empty($new_password) ? password_hash($new_password, PASSWORD_DEFAULT) : null; // Hash the password

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
