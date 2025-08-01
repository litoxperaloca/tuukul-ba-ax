<?php
// app/controllers/GroupController.php

class GroupController {
    private $grupoModel;
    private $estudianteGrupoModel;
    private $userModel;
    private $cursoModel; // Para obtener información del curso al crear/ver grupos

    public function __construct() {
        $db = Database::getInstance()->getConnection();
        $this->grupoModel = new Grupo($db);
        $this->estudianteGrupoModel = new EstudianteGrupo($db);
        $this->userModel = new User($db);
        $this->cursoModel = new Curso($db);
    }

    /**
     * Muestra la página principal de gestión de grupos del docente.
     */
    public function index() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente') {
            header('Location: /login');
            exit;
        }

        $page_title = 'Mis Grupos';
        $id_docente = $_SESSION['user_id'];
        $grupos = $this->grupoModel->getByDocenteId($id_docente);

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/groups/index.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo grupo.
     */
    public function showCreateForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente') {
            header('Location: /login');
            exit;
        }

        $page_title = 'Crear Nuevo Grupo';
        $id_docente = $_SESSION['user_id'];

        // Obtener los cursos a los que el docente podría asociar un grupo
        // Esto requeriría una lógica más compleja para saber qué cursos imparte un docente,
        // por ahora, podríamos obtener todos los cursos activos o cursos asociados a centros del docente.
        // Por simplicidad inicial, obtendremos todos los cursos activos.
        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos

        // Obtener estudiantes disponibles para asignar (aquellos con rol 'estudiante')
        $estudiantes_disponibles = $this->userModel->getAll(); // Obtener todos los usuarios
        $estudiantes_disponibles = array_filter($estudiantes_disponibles, function($user) {
            return $user['role'] === 'estudiante';
        });


        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/groups/create.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la creación de un nuevo grupo.
     */
    public function processCreate() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login'); // O enviar JSON de error si es una llamada AJAX
            exit;
        }

        $id_docente = $_SESSION['user_id'];
        $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estudiantes_seleccionados = $_POST['estudiantes'] ?? []; // IDs de estudiantes

        if (empty($nombre_grupo) || empty($id_curso)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El nombre del grupo y el curso son obligatorios.'];
            header('Location: /dashboard/grupos/create');
            exit;
        }

        // Crear el grupo
        $grupo_id = $this->grupoModel->create($id_docente, $id_curso, $nombre_grupo, $descripcion);

        if ($grupo_id) {
            // Asociar estudiantes al grupo
            foreach ($estudiantes_seleccionados as $estudiante_id) {
                $observaciones = trim($_POST['observaciones_inclusion_' . $estudiante_id] ?? '');
                $this->estudianteGrupoModel->create((int)$estudiante_id, $grupo_id, $observaciones);
            }
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Grupo creado con éxito.'];
            header('Location: /dashboard/grupos');
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear el grupo.'];
            header('Location: /dashboard/grupos/create');
            exit;
        }
    }

    /**
     * Muestra los detalles de un grupo específico.
     * @param int $id ID del grupo.
     */
    public function view($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente') {
            header('Location: /login');
            exit;
        }

        $page_title = 'Detalle del Grupo';
        $id_docente = $_SESSION['user_id'];
        $grupo = $this->grupoModel->getById((int)$id);

        // Verificar que el grupo existe y pertenece al docente actual
        if (!$grupo || $grupo['id_docente'] !== $id_docente) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Grupo no encontrado o no tienes permiso para verlo.'];
            header('Location: /dashboard/grupos');
            exit;
        }

        $estudiantes_en_grupo = $this->estudianteGrupoModel->getEstudiantesByGrupoId((int)$id);

        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/groups/view.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Muestra el formulario para editar un grupo existente.
     * @param int $id ID del grupo.
     */
    public function showEditForm($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente') {
            header('Location: /login');
            exit;
        }

        $page_title = 'Editar Grupo';
        $id_docente = $_SESSION['user_id'];
        $grupo = $this->grupoModel->getById((int)$id);

        if (!$grupo || $grupo['id_docente'] !== $id_docente) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Grupo no encontrado o no tienes permiso para editarlo.'];
            header('Location: /dashboard/grupos');
            exit;
        }

        $cursos = $this->cursoModel->getAll(null, true); // Obtener todos los cursos activos
        $estudiantes_disponibles = $this->userModel->getAll();
        $estudiantes_disponibles = array_filter($estudiantes_disponibles, function($user) {
            return $user['role'] === 'estudiante';
        });
        $estudiantes_en_grupo = $this->estudianteGrupoModel->getEstudiantesByGrupoId((int)$id);
        $estudiantes_en_grupo_ids = array_column($estudiantes_en_grupo, 'id_estudiante');


        require_once '../app/views/_partials/header.php';
        require_once '../app/views/dashboard/groups/edit.php';
        require_once '../app/views/_partials/footer.php';
    }

    /**
     * Procesa la actualización de un grupo.
     */
    public function processUpdate($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_docente = $_SESSION['user_id'];
        $grupo = $this->grupoModel->getById((int)$id);

        if (!$grupo || $grupo['id_docente'] !== $id_docente) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Grupo no encontrado o no tienes permiso para editarlo.'];
            header('Location: /dashboard/grupos');
            exit;
        }

        $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estudiantes_seleccionados = $_POST['estudiantes'] ?? []; // IDs de estudiantes

        if (empty($nombre_grupo) || empty($id_curso)) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El nombre del grupo y el curso son obligatorios.'];
            header('Location: /dashboard/grupos/edit/' . $id);
            exit;
        }

        // Actualizar el grupo principal
        $updated = $this->grupoModel->update($id, $id_docente, $id_curso, $nombre_grupo, $descripcion);

        // Gestionar estudiantes: eliminar los que ya no están y añadir los nuevos
        $current_estudiantes_in_group = array_column($this->estudianteGrupoModel->getEstudiantesByGrupoId($id), 'id_estudiante');

        // Estudiantes a eliminar
        $to_remove = array_diff($current_estudiantes_in_group, $estudiantes_seleccionados);
        foreach ($to_remove as $estudiante_id) {
            $this->estudianteGrupoModel->delete((int)$estudiante_id, (int)$id);
        }

        // Estudiantes a añadir o actualizar
        foreach ($estudiantes_seleccionados as $estudiante_id) {
            $observaciones = trim($_POST['observaciones_inclusion_' . $estudiante_id] ?? '');
            // Intentar crear/actualizar. Si ya existe, updateObservacionesInclusion lo actualizará.
            $created = $this->estudianteGrupoModel->create((int)$estudiante_id, (int)$id, $observaciones);
            if (!$created) {
                // Si no se pudo crear (probablemente ya existía), intentar actualizar
                $this->estudianteGrupoModel->updateObservacionesInclusion((int)$estudiante_id, (int)$id, $observaciones);
            }
        }


        if ($updated || !empty($to_remove) || !empty($estudiantes_seleccionados)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Grupo actualizado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'info', 'text' => 'No se realizaron cambios en el grupo.'];
        }
        header('Location: /dashboard/grupos/view/' . $id);
        exit;
    }

    /**
     * Procesa la eliminación de un grupo.
     * @param int $id ID del grupo a eliminar.
     */
    public function processDelete($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'docente' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $id_docente = $_SESSION['user_id'];
        $grupo = $this->grupoModel->getById((int)$id);

        if (!$grupo || $grupo['id_docente'] !== $id_docente) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Grupo no encontrado o no tienes permiso para eliminarlo.'];
            header('Location: /dashboard/grupos');
            exit;
        }

        if ($this->grupoModel->delete((int)$id)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Grupo eliminado con éxito.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el grupo.'];
        }
        header('Location: /dashboard/grupos');
        exit;
    }
}
