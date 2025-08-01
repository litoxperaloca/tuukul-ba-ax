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
