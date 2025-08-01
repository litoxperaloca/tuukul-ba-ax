<?php
// app/controllers/AdminController.php (Actualizado con Constructor de Formularios)

class AdminController {
    private $userModel;
    private $paisModel;
    private $centroEducativoModel;
    private $planEstudioModel;
    private $cursoModel;
    private $cursoCentroEducativoModel;
    private $contenidoCurricularModel;
    private $formularioDinamicoModel;
    private $openaiAssistantConfigModel;

    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->userModel = new User($db);
        $this->paisModel = new Pais($db);
        $this->centroEducativoModel = new CentroEducativo($db);
        $this->planEstudioModel = new PlanEstudio($db);
        $this->cursoModel = new Curso($db);
        $this->cursoCentroEducativoModel = new CursoCentroEducativo($db);
        $this->contenidoCurricularModel = new ContenidoCurricular($db);
        $this->formularioDinamicoModel = new FormularioDinamico($db);
        $this->openaiAssistantConfigModel = new OpenAIAssistantConfig($db);
    }

    /**
     * Muestra el panel principal de administración (gestión de usuarios).
     * Ruta: /admin/dashboard
     */
    public function index() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Usuarios';
        $users = $this->userModel->getAll();
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/index.php'; // Vista existente para usuarios
        require_once '../app/views/_partials/footer.php';
    }

    // --- Métodos para la Gestión de Países ---

    /**
     * Muestra la lista de países en el panel de administración.
     * Ruta: /admin/paises
     */
    public function listPaises() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Países';
        $paises = $this->paisModel->getAll(); // Obtener todos los países
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/paises/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo país.
     * Ruta: /admin/paises/create
     */
    public function showCreatePaisForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear País';
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/paises/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo país.
     * Ruta: /admin/paises/process-create
     * Método: POST
     */
    public function processCreatePais() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $codigo_iso = trim(strtoupper($_POST['codigo_iso'] ?? ''));
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || empty($codigo_iso)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El nombre y el código ISO son obligatorios.'];
            header('Location: /admin/paises/create');
            exit;
        }

        if ($this->paisModel->create($nombre, $codigo_iso, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'País creado con éxito.'];
            header('Location: /admin/paises');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el país. Asegúrate de que el código ISO no esté duplicado.'];
            header('Location: /admin/paises/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un país existente.
     * Ruta: /admin/paises/edit/{id}
     * @param int $id ID del país a editar.
     */
    public function showEditPaisForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar País';
        $pais = $this->paisModel->getById((int)$id);

        if (!$pais) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'País no encontrado.'];
            header('Location: /admin/paises');
            exit;
        }
        $paises = $this->paisModel->getAll(true); // Obtener solo países activos para el select

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/paises/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un país existente.
     * Ruta: /admin/paises/process-update/{id}
     * Método: POST
     * @param int $id ID del país a actualizar.
     */
    public function processUpdatePais($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $codigo_iso = trim(strtoupper($_POST['codigo_iso'] ?? ''));
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || empty($codigo_iso)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El nombre y el código ISO son obligatorios.'];
            header('Location: /admin/paises/edit/' . $id);
            exit;
        }

        if ($this->paisModel->update((int)$id, $nombre, $codigo_iso, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'País actualizado con éxito.'];
            header('Location: /admin/paises');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar el país. Asegúrate de que el código ISO no esté duplicado.'];
            header('Location: /admin/paises/edit/' . $id);
            exit;
        }
    }

    /**
     * Procesa la eliminación de un país.
     * Ruta: /admin/paises/process-delete/{id}
     * Método: POST
     * @param int $id ID del país a eliminar.
     */
    public function processDeletePais($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Antes de eliminar, verificar si hay centros educativos asociados a este país
        $centrosExistentes = $this->centroEducativoModel->getAll((int)$id);

        if (!empty($centrosExistentes)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'No se puede eliminar el país porque tiene centros educativos asociados. Elimina los centros primero.'];
            header('Location: /admin/paises');
            exit;
        }

        if ($this->paisModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'País eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el país.'];
        }
        header('Location: /admin/paises');
        exit;
    }

    // --- Métodos para la Gestión de Centros Educativos ---

    /**
     * Muestra la lista de centros educativos en el panel de administración.
     * Ruta: /admin/centros-educativos
     */
    public function listCentrosEducativos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Centros Educativos';
        $centros = $this->centroEducativoModel->getAll(); // Obtener todos los centros
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/centros_educativos/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo centro educativo.
     * Ruta: /admin/centros-educativos/create
     */
    public function showCreateCentroEducativoForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear Centro Educativo';
        $paises = $this->paisModel->getAll(true); // Obtener solo países activos para el select
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/centros_educativos/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo centro educativo.
     * Ruta: /admin/centros-educativos/process-create
     * Método: POST
     */
    public function processCreateCentroEducativo() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_pais = (int)($_POST['id_pais'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $cct = trim($_POST['cct'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($id_pais) || empty($nombre)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El país y el nombre del centro educativo son obligatorios.'];
            header('Location: /admin/centros-educativos/create');
            exit;
        }

        if ($this->centroEducativoModel->create($id_pais, $nombre, $cct, $direccion, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Centro educativo creado con éxito.'];
            header('Location: /admin/centros-educativos');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el centro educativo. Asegúrate de que el CCT no esté duplicado.'];
            header('Location: /admin/centros-educativos/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un centro educativo existente.
     * Ruta: /admin/centros-educativos/edit/{id}
     * @param int $id ID del centro educativo a editar.
     */
    public function showEditCentroEducativoForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar Centro Educativo';
        $centro = $this->centroEducativoModel->getById((int)$id);

        if (!$centro) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Centro educativo no encontrado.'];
            header('Location: /admin/centros-educativos');
            exit;
        }
        $paises = $this->paisModel->getAll(true); // Obtener solo países activos para el select

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/centros_educativos/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un centro educativo existente.
     * Ruta: /admin/centros-educativos/process-update/{id}
     * Método: POST
     * @param int $id ID del centro educativo a actualizar.
     */
    public function processUpdateCentroEducativo($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_pais = (int)($_POST['id_pais'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $cct = trim($_POST['cct'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($id_pais) || empty($nombre)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El país y el nombre del centro educativo son obligatorios.'];
            header('Location: /admin/centros-educativos/edit/' . $id);
            exit;
        }

        if ($this->centroEducativoModel->update((int)$id, $id_pais, $nombre, $cct, $direccion, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Centro educativo actualizado con éxito.'];
            header('Location: /admin/centros-educativos');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar el centro educativo. Asegúrate de que el CCT no esté duplicado.'];
            header('Location: /admin/centros-educativos/edit/' . $id);
            exit;
        }
    }

    /**
     * Procesa la eliminación de un centro educativo.
     * Ruta: /admin/centros-educativos/process-delete/{id}
     * Método: POST
     * @param int $id ID del centro educativo a eliminar.
     */
    public function processDeleteCentroEducativo($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Antes de eliminar, verificar si hay cursos asociados a este centro educativo
        $db = Database::getInstance()->getConnection();
        $cursoCentroEducativoModel = new CursoCentroEducativo($db);
        $cursosAsociados = $cursoCentroEducativoModel->getCursosByCentroEducativoId((int)$id);

        if (!empty($cursosAsociados)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'No se puede eliminar el centro educativo porque tiene cursos asociados. Desvincula los cursos primero.'];
            header('Location: /admin/centros-educativos');
            exit;
        }


        if ($this->centroEducativoModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Centro educativo eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el centro educativo.'];
        }
        header('Location: /admin/centros-educativos');
        exit;
    }

    // --- Métodos para la Gestión de Planes de Estudio ---

    /**
     * Muestra la lista de planes de estudio en el panel de administración.
     * Ruta: /admin/planes-estudio
     */
    public function listPlanesEstudio() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Planes de Estudio';
        $planes = $this->planEstudioModel->getAll(); // Obtener todos los planes de estudio
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/planes_estudio/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo plan de estudio.
     * Ruta: /admin/planes-estudio/create
     */
    public function showCreatePlanEstudioForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear Plan de Estudio';
        $paises = $this->paisModel->getAll(true); // Obtener solo países activos para el select
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/planes_estudio/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo plan de estudio.
     * Ruta: /admin/planes-estudio/process-create
     * Método: POST
     */
    public function processCreatePlanEstudio() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_pais = (int)($_POST['id_pais'] ?? 0);
        $nombre_plan = trim($_POST['nombre_plan'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $anio_vigencia_inicio = (int)($_POST['anio_vigencia_inicio'] ?? 0);
        $anio_vigencia_fin = !empty($_POST['anio_vigencia_fin']) ? (int)$_POST['anio_vigencia_fin'] : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($id_pais) || empty($nombre_plan) || empty($anio_vigencia_inicio)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El país, nombre del plan y año de inicio de vigencia son obligatorios.'];
            header('Location: /admin/planes-estudio/create');
            exit;
        }

        if ($this->planEstudioModel->create($id_pais, $nombre_plan, $descripcion, $anio_vigencia_inicio, $anio_vigencia_fin, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Plan de estudio creado con éxito.'];
            header('Location: /admin/planes-estudio');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el plan de estudio. Asegúrate de que el nombre del plan no esté duplicado para este país y año de inicio.'];
            header('Location: /admin/planes-estudio/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un plan de estudio existente.
     * Ruta: /admin/planes-estudio/edit/{id}
     * @param int $id ID del plan de estudio a editar.
     */
    public function showEditPlanEstudioForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar Plan de Estudio';
        $plan = $this->planEstudioModel->getById((int)$id);

        if (!$plan) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Plan de estudio no encontrado.'];
            header('Location: /admin/planes-estudio');
            exit;
        }
        $paises = $this->paisModel->getAll(true); // Obtener solo países activos para el select

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/planes_estudio/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un plan de estudio existente.
     * Ruta: /admin/planes-estudio/process-update/{id}
     * Método: POST
     * @param int $id ID del plan de estudio a actualizar.
     */
    public function processUpdatePlanEstudio($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_pais = (int)($_POST['id_pais'] ?? 0);
        $nombre_plan = trim($_POST['nombre_plan'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $anio_vigencia_inicio = (int)($_POST['anio_vigencia_inicio'] ?? 0);
        $anio_vigencia_fin = !empty($_POST['anio_vigencia_fin']) ? (int)$_POST['anio_vigencia_fin'] : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($id_pais) || empty($nombre_plan) || empty($anio_vigencia_inicio)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El país, nombre del plan y año de inicio de vigencia son obligatorios.'];
            header('Location: /admin/planes-estudio/edit/' . $id);
            exit;
        }

        if ($this->planEstudioModel->update((int)$id, $id_pais, $nombre_plan, $descripcion, $anio_vigencia_inicio, $anio_vigencia_fin, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Plan de estudio actualizado con éxito.'];
            header('Location: /admin/planes-estudio');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar el plan de estudio. Asegúrate de que el nombre del plan no esté duplicado para este país y año de inicio.'];
            header('Location: /admin/planes-estudio/edit/' . $id);
            exit;
        }
    }

    /**
     * Procesa la eliminación de un plan de estudio.
     * Ruta: /admin/planes-estudio/process-delete/{id}
     * Método: POST
     * @param int $id ID del plan de estudio a eliminar.
     */
    public function processDeletePlanEstudio($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Antes de eliminar, verificar si hay cursos asociados a este plan de estudio
        $db = Database::getInstance()->getConnection();
        $cursoModel = new Curso($db);
        $cursosAsociados = $cursoModel->getAll((int)$id); // getAll filtra por id_plan_estudio

        if (!empty($cursosAsociados)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'No se puede eliminar el plan de estudio porque tiene cursos asociados. Elimina los cursos primero.'];
            header('Location: /admin/planes-estudio');
            exit;
        }

        if ($this->planEstudioModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Plan de estudio eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el plan de estudio.'];
        }
        header('Location: /admin/planes-estudio');
        exit;
    }

    // --- Métodos para la Gestión de Cursos ---

    /**
     * Muestra la lista de cursos en el panel de administración.
     * Ruta: /admin/cursos
     */
    public function listCursos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Cursos';
        $cursos = $this->cursoModel->getAll(); // Obtener todos los cursos
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/cursos/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo curso.
     * Ruta: /admin/cursos/create
     */
    public function showCreateCursoForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear Curso';
        $planes_estudio = $this->planEstudioModel->getAll(null, true); // Obtener todos los planes de estudio activos
        $centros_educativos = $this->centroEducativoModel->getAll(null, true); // Obtener todos los centros educativos activos
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/cursos/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo curso.
     * Ruta: /admin/cursos/process-create
     * Método: POST
     */
    public function processCreateCurso() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_plan_estudio = (int)($_POST['id_plan_estudio'] ?? 0);
        $nivel = trim($_POST['nivel'] ?? '');
        $grado = trim($_POST['grado'] ?? '');
        $asignatura = trim($_POST['asignatura'] ?? '');
        $fase = trim($_POST['fase'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;
        $centros_seleccionados = $_POST['centros_educativos'] ?? []; // IDs de centros asociados

        if (empty($id_plan_estudio) || empty($nivel) || empty($grado) || empty($asignatura)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El plan de estudio, nivel, grado y asignatura son obligatorios.'];
            header('Location: /admin/cursos/create');
            exit;
        }

        $curso_id = $this->cursoModel->create($id_plan_estudio, $nivel, $grado, $asignatura, $fase, $descripcion, $activo);

        if ($curso_id) {
            // Asociar centros educativos al curso
            foreach ($centros_seleccionados as $centro_id) {
                $this->cursoCentroEducativoModel->create($curso_id, (int)$centro_id);
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Curso creado con éxito.'];
            header('Location: /admin/cursos');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el curso. Asegúrate de que la combinación de plan, nivel, grado y asignatura no esté duplicada.'];
            header('Location: /admin/cursos/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un curso existente.
     * Ruta: /admin/cursos/edit/{id}
     * @param int $id ID del curso a editar.
     */
    public function showEditCursoForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar Curso';
        $curso = $this->cursoModel->getById((int)$id);

        if (!$curso) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Curso no encontrado.'];
            header('Location: /admin/cursos');
            exit;
        }
        $planes_estudio = $this->planEstudioModel->getAll(null, true);
        $centros_educativos = $this->centroEducativoModel->getAll(null, true);
        $centros_asociados_ids = array_column($this->cursoCentroEducativoModel->getCentrosByCursoId((int)$id), 'id_centro_educativo');

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/cursos/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un curso existente.
     * Ruta: /admin/cursos/process-update/{id}
     * Método: POST
     * @param int $id ID del curso a actualizar.
     */
    public function processUpdateCurso($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_plan_estudio = (int)($_POST['id_plan_estudio'] ?? 0);
        $nivel = trim($_POST['nivel'] ?? '');
        $grado = trim($_POST['grado'] ?? '');
        $asignatura = trim($_POST['asignatura'] ?? '');
        $fase = trim($_POST['fase'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;
        $centros_seleccionados = $_POST['centros_educativos'] ?? [];

        if (empty($id_plan_estudio) || empty($nivel) || empty($grado) || empty($asignatura)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El plan de estudio, nivel, grado y asignatura son obligatorios.'];
            header('Location: /admin/cursos/edit/' . $id);
            exit;
        }

        $updated = $this->cursoModel->update((int)$id, $id_plan_estudio, $nivel, $grado, $asignatura, $fase, $descripcion, $activo);

        // Gestionar asociaciones con centros educativos
        $current_centros_asociados_ids = array_column($this->cursoCentroEducativoModel->getCentrosByCursoId((int)$id), 'id_centro_educativo');

        // Centros a eliminar
        $to_remove = array_diff($current_centros_asociados_ids, $centros_seleccionados);
        foreach ($to_remove as $centro_id) {
            $this->cursoCentroEducativoModel->delete((int)$id, (int)$centro_id);
        }

        // Centros a añadir
        $to_add = array_diff($centros_seleccionados, $current_centros_asociados_ids);
        foreach ($to_add as $centro_id) {
            $this->cursoCentroEducativoModel->create((int)$id, (int)$centro_id);
        }

        if ($updated || !empty($to_remove) || !empty($to_add)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Curso actualizado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'info', 'text' => 'No se realizaron cambios en el curso.'];
        }
        header('Location: /admin/cursos');
        exit;
    }

    /**
     * Procesa la eliminación de un curso.
     * Ruta: /admin/cursos/process-delete/{id}
     * Método: POST
     * @param int $id ID del curso a eliminar.
     */
    public function processDeleteCurso($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        // Antes de eliminar, verificar si hay planeaciones o grupos asociados a este curso
        // Nota: PlaneacionModel::getByCursoId y GrupoModel::getByCursoId (para todos los docentes)
        // son necesarios para estas verificaciones.
        $planeacionesAsociadas = $this->planeacionModel->getByCursoId((int)$id); // Asumiendo que existe este método
        $gruposAsociados = $this->grupoModel->getByCursoId(null, (int)$id); // Asumiendo que getByCursoId puede tomar null para id_docente

        if (!empty($planeacionesAsociadas) || !empty($gruposAsociados)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'No se puede eliminar el curso porque tiene planeaciones o grupos asociados. Elimina las planeaciones y grupos primero.'];
            header('Location: /admin/cursos');
            exit;
        }

        if ($this->cursoModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Curso eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el curso.'];
        }
        header('Location: /admin/cursos');
        exit;
    }

    // --- Métodos para la Gestión de Contenidos Curriculares ---

    /**
     * Muestra la lista de contenidos curriculares en el panel de administración.
     * Ruta: /admin/contenidos-curriculares
     */
    public function listContenidosCurriculares() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Contenidos Curriculares';
        $contenidos = $this->contenidoCurricularModel->getAllWithCursoInfo();

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/contenidos_curriculares/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo contenido curricular.
     * Ruta: /admin/contenidos-curriculares/create
     */
    public function showCreateContenidoCurricularForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear Contenido Curricular';
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/contenidos_curriculares/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo contenido curricular.
     * Ruta: /admin/contenidos-curriculares/process-create
     * Método: POST
     */
    public function processCreateContenidoCurricular() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $nombre_contenido = trim($_POST['nombre_contenido'] ?? '');
        $pda_descripcion = trim($_POST['pda_descripcion'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($id_curso) || empty($nombre_contenido) || empty($tipo)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso, nombre del contenido y tipo son obligatorios.'];
            header('Location: /admin/contenidos-curriculares/create');
            exit;
        }

        if ($this->contenidoCurricularModel->create($id_curso, $nombre_contenido, $pda_descripcion, $tipo, $orden, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Contenido curricular creado con éxito.'];
            header('Location: /admin/contenidos-curriculares');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el contenido curricular.'];
            header('Location: /admin/contenidos-curriculares/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un contenido curricular existente.
     * Ruta: /admin/contenidos-curriculares/edit/{id}
     * @param int $id ID del contenido curricular a editar.
     */
    public function showEditContenidoCurricularForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar Contenido Curricular';
        $contenido = $this->contenidoCurricularModel->getById((int)$id);

        if (!$contenido) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Contenido curricular no encontrado.'];
            header('Location: /admin/contenidos-curriculares');
            exit;
        }
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/contenidos_curriculares/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un contenido curricular existente.
     * Ruta: /admin/contenidos-curriculares/process-update/{id}
     * Método: POST
     * @param int $id ID del contenido curricular a actualizar.
     */
    public function processUpdateContenidoCurricular($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $nombre_contenido = trim($_POST['nombre_contenido'] ?? '');
        $pda_descripcion = trim($_POST['pda_descripcion'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($id_curso) || empty($nombre_contenido) || empty($tipo)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso, nombre del contenido y tipo son obligatorios.'];
            header('Location: /admin/contenidos-curriculares/edit/' . $id);
            exit;
        }

        if ($this->contenidoCurricularModel->update((int)$id, $id_curso, $nombre_contenido, $pda_descripcion, $tipo, $orden, $activo)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Contenido curricular actualizado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar el contenido curricular.'];
        }
        header('Location: /admin/contenidos-curriculares');
        exit;
    }

    /**
     * Procesa la eliminación de un contenido curricular.
     * Ruta: /admin/contenidos-curriculares/process-delete/{id}
     * Método: POST
     * @param int $id ID del contenido curricular a eliminar.
     */
    public function processDeleteContenidoCurricular($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        if ($this->contenidoCurricularModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Contenido curricular eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el contenido curricular.'];
        }
        header('Location: /admin/contenidos-curriculares');
        exit;
    }

    // --- Métodos para la Gestión de Formularios Dinámicos ---

    /**
     * Muestra la lista de formularios dinámicos en el panel de administración.
     * Ruta: /admin/formularios-dinamicos
     */
    public function listFormulariosDinamicos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Formularios Dinámicos';
        $formularios = $this->formularioDinamicoModel->getAllWithCursoInfo();

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/formularios_dinamicos/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo formulario dinámico.
     * Ruta: /admin/formularios-dinamicos/create
     */
    public function showCreateFormularioDinamicoForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear Formulario Dinámico';
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        // Inicializar un esquema vacío para el constructor
        $schema_json_initial = json_encode(['fields' => []], JSON_PRETTY_PRINT);
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/formularios_dinamicos/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo formulario dinámico.
     * Ruta: /admin/formularios-dinamicos/process-create
     * Método: POST
     */
    public function processCreateFormularioDinamico() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $schema_json_str = trim($_POST['schema_json_output'] ?? '');

        // Si la cadena está vacía, asegúrate de que sea un JSON de esquema vacío válido
        if (empty($schema_json_str)) {
            $schema_json_str = '{"fields":[]}';
        }

        $schema_json = json_decode($schema_json_str, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("AdminController: Error al decodificar schema_json desde POST. Error: " . json_last_error_msg() . ". String recibido: '" . $schema_json_str . "'");
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: El esquema del formulario no es válido.'];
            header('Location: /admin/formularios-dinamicos/create');
            exit;
        }

        // Asegúrate de que $schema_json sea un array y tenga la clave 'fields'
        if (!is_array($schema_json) || !isset($schema_json['fields'])) {
            $schema_json = ['fields' => []];
        }

        if (empty($id_curso) || empty($nombre)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso y el nombre son obligatorios.'];
            header('Location: /admin/formularios-dinamicos/create');
            exit;
        }

        if ($this->formularioDinamicoModel->create($id_curso, $nombre, $schema_json)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Formulario dinámico creado con éxito.'];
            header('Location: /admin/formularios-dinamicos');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el formulario dinámico. Asegúrate de que no haya otro formulario para este curso.'];
            header('Location: /admin/formularios-dinamicos/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un formulario dinámico existente.
     * Ruta: /admin/formularios-dinamicos/edit/{id}
     * @param int $id ID del formulario dinámico a editar.
     */
    public function showEditFormularioDinamicoForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar Formulario Dinámico';
        $formulario = $this->formularioDinamicoModel->getById((int)$id);

        if (!$formulario) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Formulario dinámico no encontrado.'];
            header('Location: /admin/formularios-dinamicos');
            exit;
        }
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        // Pasar el esquema JSON existente para que el constructor lo cargue
        $schema_json_initial = json_encode($formulario['schema_json'], JSON_PRETTY_PRINT);

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/formularios_dinamicos/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un formulario dinámico existente.
     * Ruta: /admin/formularios-dinamicos/process-update/{id}
     * Método: POST
     */
    public function processUpdateFormularioDinamico($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $schema_json_str = trim($_POST['schema_json_output'] ?? '');

        // Si la cadena está vacía, asegúrate de que sea un JSON de esquema vacío válido
        if (empty($schema_json_str)) {
            $schema_json_str = '{"fields":[]}';
        }

        $schema_json = json_decode($schema_json_str, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("AdminController: Error al decodificar schema_json desde POST. Error: " . json_last_error_msg() . ". String recibido: '" . $schema_json_str . "'");
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: El esquema del formulario no es válido.'];
            header('Location: /admin/formularios-dinamicos/edit/' . $id);
            exit;
        }
        
        // Asegúrate de que $schema_json sea un array y tenga la clave 'fields'
        if (!is_array($schema_json) || !isset($schema_json['fields'])) {
            $schema_json = ['fields' => []];
        }

        if (empty($id_curso) || empty($nombre)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso y el nombre son obligatorios.'];
            header('Location: /admin/formularios-dinamicos/edit/' . $id);
            exit;
        }

        if ($this->formularioDinamicoModel->update((int)$id, $id_curso, $nombre, $schema_json)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Formulario dinámico actualizado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar el formulario dinámico. Asegúrate de que no haya otro formulario para este curso.'];
        }
        header('Location: /admin/formularios-dinamicos');
        exit;
    }

    /**
     * Procesa la eliminación de un formulario dinámico.
     * Ruta: /admin/formularios-dinamicos/process-delete/{id}
     * Método: POST
     * @param int $id ID del formulario dinámico a eliminar.
     */
    public function processDeleteFormularioDinamico($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        if ($this->formularioDinamicoModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Formulario dinámico eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el formulario dinámico.'];
        }
        header('Location: /admin/formularios-dinamicos');
        exit;
    }

    // --- Métodos para la Gestión de Configuraciones de Asistentes IA ---

    /**
     * Muestra la lista de configuraciones de asistentes IA en el panel de administración.
     * Ruta: /admin/asistentes-ia
     */
    public function listOpenAIAssistantConfigs() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Asistentes IA';
        $configs = $this->openaiAssistantConfigModel->getAllWithCursoInfo();

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/openai_assistant_configs/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear una nueva configuración de asistente IA.
     * Ruta: /admin/asistentes-ia/create
     */
    public function showCreateOpenAIAssistantConfigForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Crear Configuración de Asistente IA';
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/openai_assistant_configs/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de una nueva configuración de asistente IA.
     * Ruta: /admin/asistentes-ia/process-create
     * Método: POST
     */
    public function processCreateOpenAIAssistantConfig() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $assistant_id = trim($_POST['assistant_id'] ?? '');
        $vector_store_id = trim($_POST['vector_store_id'] ?? '');
        $instrucciones_adicionales = trim($_POST['instrucciones_adicionales'] ?? '');

        if (empty($id_curso) || empty($assistant_id)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso y el Assistant ID son obligatorios.'];
            header('Location: /admin/asistentes-ia/create');
            exit;
        }

        if ($this->openaiAssistantConfigModel->create($id_curso, $assistant_id, $vector_store_id, $instrucciones_adicionales)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Configuración de asistente creada con éxito.'];
            header('Location: /admin/asistentes-ia');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear la configuración de asistente. Asegúrate de que no haya otra configuración para este curso.'];
            header('Location: /admin/asistentes-ia/create');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar una configuración de asistente IA existente.
     * Ruta: /admin/asistentes-ia/edit/{id}
     * @param int $id ID de la configuración a editar.
     */
    public function showEditOpenAIAssistantConfigForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        $page_title = 'Panel de Administración - Editar Configuración de Asistente IA';
        $config = $this->openaiAssistantConfigModel->getById((int)$id);

        if (!$config) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Configuración de asistente IA no encontrada.'];
            header('Location: /admin/asistentes-ia');
            exit;
        }
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/admin/openai_assistant_configs/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de una configuración de asistente IA existente.
     * Ruta: /admin/asistentes-ia/process-update/{id}
     * Método: POST
     * @param int $id ID de la configuración a actualizar.
     */
    public function processUpdateOpenAIAssistantConfig($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $assistant_id = trim($_POST['assistant_id'] ?? '');
        $vector_store_id = trim($_POST['vector_store_id'] ?? '');
        $instrucciones_adicionales = trim($_POST['instrucciones_adicionales'] ?? '');

        if (empty($id_curso) || empty($assistant_id)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso y el Assistant ID son obligatorios.'];
            header('Location: /admin/asistentes-ia/edit/' . $id);
            exit;
        }

        if ($this->openaiAssistantConfigModel->update((int)$id, $id_curso, $assistant_id, $vector_store_id, $instrucciones_adicionales)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Configuración de asistente actualizada con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar la configuración de asistente. Asegúrate de que no haya otra configuración para este curso.'];
        }
        header('Location: /admin/asistentes-ia');
        exit;
    }

    /**
     * Procesa la eliminación de una configuración de asistente IA.
     * Ruta: /admin/asistentes-ia/process-delete/{id}
     * Método: POST
     * @param int $id ID de la configuración a eliminar.
     */
    public function processDeleteOpenAIAssistantConfig($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        if ($this->openaiAssistantConfigModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Configuración de asistente eliminada con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar la configuración de asistente.'];
        }
        header('Location: /admin/asistentes-ia');
        exit;
    }
}
