<?php
// Archivo: app/controllers/ApiController.php (Versión Mejorada con prompt simplificado y campos actualizados)

class ApiController {
    private $planeacionModel;
    private $userModel;
    private $paisModel;
    private $centroEducativoModel;
    private $planEstudioModel;
    private $cursoModel;
    private $contenidoCurricularModel;
    private $grupoModel;
    private $estudianteGrupoModel;
    private $openaiAssistantConfigModel;
    private $formularioDinamicoModel;

    public function __construct() {
        $db = Database::getInstance()->getConnection();

        $this->planeacionModel = new Planeacion($db);
        $this->userModel = new User($db);
        $this->paisModel = new Pais($db);
        $this->centroEducativoModel = new CentroEducativo($db);
        $this->planEstudioModel = new PlanEstudio($db);
        $this->cursoModel = new Curso($db);
        $this->contenidoCurricularModel = new ContenidoCurricular($db);
        $this->grupoModel = new Grupo($db);
        $this->estudianteGrupoModel = new EstudianteGrupo($db);
        $this->openaiAssistantConfigModel = new OpenAIAssistantConfig($db);
        $this->formularioDinamicoModel = new FormularioDinamico($db);
    }

    private function isAuthorized() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'docente' || $_SESSION['user_role'] === 'admin'));
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    private function callOpenAIAPI($url, $method = 'GET', $data = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
            'OpenAI-Beta: assistants=v2'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = "Error cURL al llamar a OpenAI API ($url): " . curl_error($ch);
            error_log($error_msg);
            curl_close($ch);
            return ['error' => ['message' => 'Error de conexión con la API de OpenAI. Detalles: ' . $error_msg]];
        }
        curl_close($ch);

        $api_data = json_decode($response, true);

        if ($http_code >= 400) {
            $error_message = $api_data['error']['message'] ?? 'Error desconocido de la API de OpenAI.';
            error_log("Error de la API de OpenAI (HTTP $http_code) en $url: " . $error_message);
            return ['error' => ['message' => "Error de la API de OpenAI: $error_message (Código: $http_code)"]];
        }

        return $api_data;
    }

    private function validateInt($value, $fieldName, $required = true, $min = null, $max = null) {
        $value = filter_var(trim($value), FILTER_SANITIZE_NUMBER_INT);
        if ($required && empty($value) && $value !== '0') {
            throw new Exception("El campo '$fieldName' es obligatorio.");
        }
        if (!is_numeric($value)) {
            if ($required) {
                throw new Exception("El campo '$fieldName' debe ser un número entero válido.");
            }
            return null;
        }
        $intValue = (int)$value;
        if ($min !== null && $intValue < $min) {
            throw new Exception("El campo '$fieldName' debe ser mayor o igual a $min.");
        }
        if ($max !== null && $intValue > $max) {
            throw new Exception("El campo '$fieldName' debe ser menor o igual a $max.");
        }
        return $intValue;
    }

  
    private function validateString($value, $fieldName, $required = true, $minLength = 0, $maxLength = 255) {
        $value = trim($value);
        // Solo aplicar htmlspecialchars si no es un campo JSON que se decodificará
        // La lógica para alumnos_nee_json y dynamic_form_data_json se maneja por separado.
        // Este método es para cadenas de texto generales.
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if ($required && empty($value)) {
            throw new Exception("El campo '$fieldName' es obligatorio.");
        }
        if (!empty($value) && mb_strlen($value) < $minLength) {
            throw new Exception("El campo '$fieldName' debe tener al menos $minLength caracteres.");
        }
        if (!empty($value) && mb_strlen($value) > $maxLength) {
            throw new Exception("El campo '$fieldName' no puede exceder los $maxLength caracteres.");
        }
        return $value;
    }

    private function validateBool($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getCentrosEducativos() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $pais_id = $this->validateInt($_GET['pais_id'] ?? null, 'ID de País', true);
            $centros = $this->centroEducativoModel->getAll($pais_id, true);
            $this->sendJsonResponse(['success' => true, 'centros' => $centros]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getPlanesEstudio() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $centro_educativo_id = $this->validateInt($_GET['centro_educativo_id'] ?? null, 'ID de Centro Educativo', true);
            $planes = [];
            $centro = $this->centroEducativoModel->getById($centro_educativo_id);
            if ($centro && isset($centro['id_pais'])) {
                $planes = $this->planEstudioModel->getAll((int)$centro['id_pais'], true);
            }
            $this->sendJsonResponse(['success' => true, 'planes' => $planes]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getCursos() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $plan_estudio_id = $this->validateInt($_GET['plan_estudio_id'] ?? null, 'ID de Plan de Estudio', true);
            $cursos = $this->cursoModel->getAll($plan_estudio_id, true);
            $this->sendJsonResponse(['success' => true, 'cursos' => $cursos]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getContenidosCurriculares() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $curso_id = $this->validateInt($_GET['curso_id'] ?? null, 'ID de Curso', true);
            $tipo = $this->validateString($_GET['tipo'] ?? null, 'Tipo de Contenido', true);
            if (!in_array($tipo, ['contenido', 'eje_articulador'])) {
                throw new Exception("Tipo de contenido inválido.");
            }
            $contenidos = $this->contenidoCurricularModel->getByCursoId($curso_id, $tipo, true);
            $this->sendJsonResponse(['success' => true, 'contenidos' => $contenidos]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getGrupos() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $id_docente = $_SESSION['user_id'];
            $curso_id = $this->validateInt($_GET['curso_id'] ?? null, 'ID de Curso', true);
            $grupos = $this->grupoModel->getByDocenteId($id_docente, $curso_id);
            $this->sendJsonResponse(['success' => true, 'grupos' => $grupos]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getGrupoEstudiantes() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $grupo_id = $this->validateInt($_GET['grupo_id'] ?? null, 'ID de Grupo', true);
            $estudiantes = $this->estudianteGrupoModel->getEstudiantesByGrupoId($grupo_id);
            $this->sendJsonResponse(['success' => true, 'estudiantes' => $estudiantes]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getFormularioDinamicoByCurso() {
        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }
        try {
            $curso_id = $this->validateInt($_GET['curso_id'] ?? null, 'ID de Curso', true);
            $formulario = $this->formularioDinamicoModel->getByCursoId($curso_id);

            if ($formulario) {
                if (isset($formulario['schema_json'])) {
                    if (is_string($formulario['schema_json'])) {
                        $formulario['schema_json'] = json_decode($formulario['schema_json'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            error_log("Error al decodificar schema_json para formulario dinámico ID " . $formulario['id'] . ": " . json_last_error_msg());
                            $formulario['schema_json'] = null;
                        }
                    }
                }
                $this->sendJsonResponse(['success' => true, 'formulario' => $formulario]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'No se encontró un formulario dinámico para este curso.'], 404);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function generatePlaneacion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!$this->isAuthorized()) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Acceso no autorizado. Por favor, inicia sesión.'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido. Se esperaba POST, se recibió ' . $_SERVER['REQUEST_METHOD'] . '.'], 405);
        }

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'];

        if ($user_role === 'docente') {
            $user = $this->userModel->getById($user_id);
            if (!$user || $user['creditos'] <= 0) {
                $this->sendJsonResponse(['success' => false, 'message' => 'No tienes créditos disponibles para generar más planeaciones. Por favor, carga créditos.'], 402);
            }
        }

        $formData = $_POST;
        $validatedData = [];

        try {
            $validatedData['docente_nombre_completo'] = $this->validateString($formData['docente_nombre_completo'] ?? '', 'Nombre Completo del Docente');
            $validatedData['escuela_nombre'] = $this->validateString($formData['escuela_nombre'] ?? '', 'Nombre de la Escuela', false);
            $validatedData['escuela_cct'] = $this->validateString($formData['escuela_cct'] ?? '', 'CCT de la Escuela', false);

            $validatedData['pais_id'] = $this->validateInt($formData['pais_id'] ?? null, 'País', true);
            $validatedData['centro_educativo_id'] = $this->validateInt($formData['centro_educativo_id'] ?? null, 'Centro Educativo', true);
            $validatedData['plan_estudio_id'] = $this->validateInt($formData['plan_estudio_id'] ?? null, 'Plan de Estudio', true);
            $validatedData['curso_id'] = $this->validateInt($formData['curso_id'] ?? null, 'Curso', true);
            
            $validatedData['contenidos_curriculares_seleccionados'] = isset($formData['contenidos_curriculares_seleccionados']) && is_array($formData['contenidos_curriculares_seleccionados'])
                ? array_map(function($item) { return $this->validateString($item, 'Contenido Curricular', true, 0, 500); }, $formData['contenidos_curriculares_seleccionados'])
                : [];
            if (empty($validatedData['contenidos_curriculares_seleccionados'])) {
                throw new Exception("Debes seleccionar al menos un Contenido Curricular.");
            }

            $validatedData['num_sesiones'] = $this->validateInt($formData['num_sesiones'] ?? null, 'Número de Sesiones', true, 1, 100);
            $validatedData['duracion_sesion'] = $this->validateInt($formData['duracion_sesion'] ?? null, 'Duración por Sesión', true, 10, 180);
            $validatedData['materiales'] = $this->validateString($formData['materiales'] ?? '', 'Materiales', false, 0, 1000);
            $validatedData['sugerencias_sesiones'] = $this->validateString($formData['sugerencias_sesiones'] ?? '', 'Sugerencias para Sesiones', false, 0, 1000);
            $validatedData['observaciones_docente'] = $this->validateString($formData['observaciones_docente'] ?? '', 'Observaciones del Docente', false, 0, 1000);

            $validatedData['ejes_articuladores'] = isset($formData['ejes_articuladores']) && is_array($formData['ejes_articuladores'])
                ? array_map(function($eje) { return $this->validateString($eje, 'Eje Articulador', false, 0, 255); }, $formData['ejes_articuladores'])
                : [];

          // Validar alumnos_nee_json sin htmlspecialchars, solo trim y json_decode
            $alumnos_nee_json_raw = trim($formData['alumnos_nee_json'] ?? '[]');
            $alumnos_nee_data = json_decode($alumnos_nee_json_raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("APIController: Error al decodificar alumnos_nee_json. Error: " . json_last_error_msg() . ". Raw: " . $alumnos_nee_json_raw);
                throw new Exception("Formato JSON inválido para datos de alumnos con NEE.");
            }
            $validatedData['alumnos_nee_data'] = $alumnos_nee_data;

            // Validar dynamic_form_data_json sin htmlspecialchars, solo trim y json_decode
            $dynamic_form_data_json_raw = trim($formData['dynamic_form_data_json'] ?? '{}');
            $dynamic_form_data = json_decode($dynamic_form_data_json_raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("APIController: Error al decodificar dynamic_form_data_json. Error: " . json_last_error_msg() . ". Raw: " . $dynamic_form_data_json_raw);
                throw new Exception("Formato JSON inválido para datos de formulario dinámico.");
            }
            $validatedData['dynamic_form_data'] = $dynamic_form_data;

        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error de validación: ' . $e->getMessage()], 400);
        }

        $docente_data = $this->userModel->getById($user_id);
        $pais = $this->paisModel->getById($validatedData['pais_id']);
        $centro_educativo = $this->centroEducativoModel->getById($validatedData['centro_educativo_id']);
        $plan_estudio = $this->planEstudioModel->getById($validatedData['plan_estudio_id']);
        $curso = $this->cursoModel->getById($validatedData['curso_id']);

        if (!$pais || !$centro_educativo || !$plan_estudio || !$curso) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error: Datos curriculares no encontrados o inválidos.'], 404);
        }

        $assistant_config = $this->openaiAssistantConfigModel->getByCursoId($validatedData['curso_id']);
        if (!$assistant_config || empty($assistant_config['assistant_id'])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'No se encontró una configuración de asistente de IA para el curso seleccionado. Contacta al administrador.'], 500);
        }

        $openai_assistant_id = $assistant_config['assistant_id'];
        $openai_vector_store_id = $assistant_config['vector_store_id'] ?? null;
        $admin_instructions = $assistant_config['instrucciones_adicionales'] ?? '';

        // Construir string de Contenidos y PDAs seleccionados
        $contenidos_pdas_prompt = "";
        foreach ($validatedData['contenidos_curriculares_seleccionados'] as $item) {
            $parts = explode('||', $item);
            $contenido_name = trim($parts[0]);
            $pda_desc = trim($parts[1] ?? 'Sin PDA específico');
            $contenidos_pdas_prompt .= "- Contenido: " . htmlspecialchars($contenido_name) . "\n  PDA: " . htmlspecialchars($pda_desc) . "\n";
        }

        $ejes_str = !empty($validatedData['ejes_articuladores']) ? implode(', ', $validatedData['ejes_articuladores']) : 'No seleccionados';
        $duracion_desarrollo = $validatedData['duracion_sesion'] - 15;

         // Formatear alumnos con NEE
        $alumnos_nee_prompt = "";
        if (!empty($validatedData['alumnos_nee_data'])) {
            foreach ($validatedData['alumnos_nee_data'] as $student) {
                $alumnos_nee_prompt .= "- Nombre: " . htmlspecialchars($student['name']) . ", Necesidad: " . htmlspecialchars($student['neeType']) . "\n";
            }
        } else {
            $alumnos_nee_prompt = "Ninguno.\n";
        }

        $user_message_content = "Genera una planeación didáctica con los siguientes datos:

        - Nombre del Docente: " . htmlspecialchars($validatedData['docente_nombre_completo']) . "
        - Escuela: " . htmlspecialchars($validatedData['escuela_nombre'] ?? 'No especificada') . "
        - CCT: " . htmlspecialchars($validatedData['escuela_cct'] ?? 'No especificado') . "
        - Grado del Curso: " . htmlspecialchars($curso['grado'] ?? 'N/A') . "
        - Nivel del Curso: " . htmlspecialchars($curso['nivel'] ?? 'N/A') . "
        - Fase del Curso: " . htmlspecialchars($curso['fase'] ?? 'N/A') . "
        - Contenidos Curriculares Seleccionados:\n" . $contenidos_pdas_prompt . "
        - Ejes Articuladores: " . htmlspecialchars($ejes_str) . "
        - Número de Sesiones: " . htmlspecialchars($validatedData['num_sesiones']) . "
        - Duración por Sesión: " . htmlspecialchars($validatedData['duracion_sesion']) . " minutos
        - Duración Desarrollo Sesión: " . htmlspecialchars($duracion_desarrollo) . " minutos
        - Materiales Disponibles: " . htmlspecialchars($validatedData['materiales']) . "
        - Sugerencias para la IA: " . htmlspecialchars($validatedData['sugerencias_sesiones']) . "
        - Observaciones: " . htmlspecialchars($validatedData['observaciones_docente']) . "
        - Alumnos con NEE y Observaciones de Inclusión:\n" . $alumnos_nee_prompt . "
        - Datos Adicionales: " . json_encode($validatedData['dynamic_form_data']) . "
        " . (!empty($admin_instructions) ? "\n- Instrucciones Adicionales del Administrador: " . $admin_instructions : "");

        $ai_text_response = 'No se pudo obtener una respuesta de la IA.';

        try {
            $thread_creation_response = $this->callOpenAIAPI('https://api.openai.com/v1/threads', 'POST', []);
            if (isset($thread_creation_response['error'])) {
                throw new Exception('Error al crear el hilo de conversación con OpenAI: ' . $thread_creation_response['error']['message']);
            }
            $thread_id = $thread_creation_response['id'];

            $message_data = [
                'role' => 'user',
                'content' => $user_message_content
            ];

            $message_add_response = $this->callOpenAIAPI("https://api.openai.com/v1/threads/{$thread_id}/messages", 'POST', $message_data);
            if (isset($message_add_response['error'])) {
                throw new Exception('Error al añadir mensaje al hilo: ' . $message_add_response['error']['message']);
            }

            $run_data = [
                'assistant_id' => $openai_assistant_id,
                'tool_choice' => 'auto',
                'model' => 'gpt-4o'
            ];

            $run_creation_response = $this->callOpenAIAPI("https://api.openai.com/v1/threads/{$thread_id}/runs", 'POST', $run_data);
            if (isset($run_creation_response['error'])) {
                throw new Exception('Error al ejecutar el asistente: ' . $run_creation_response['error']['message']);
            }
            $run_id = $run_creation_response['id'];

            $status = '';
            $attempts = 0;
            $max_attempts = 90;
            $polling_interval = 2;

            while ($status !== 'completed' && $status !== 'failed' && $status !== 'cancelled' && $attempts < $max_attempts) {
                sleep($polling_interval);
                $run_status_response = $this->callOpenAIAPI("https://api.openai.com/v1/threads/{$thread_id}/runs/{$run_id}", 'GET');

                if (isset($run_status_response['error'])) {
                    error_log("Error al consultar estado del Run: " . $run_status_response['error']['message']);
                    throw new Exception("Error al consultar estado del Run de OpenAI.");
                }
                $status = $run_status_response['status'];
                $attempts++;
            }

            if ($status !== 'completed') {
                throw new Exception('El asistente no pudo completar la generación de la planeación a tiempo. Estado final: ' . $status);
            }

            $messages_response = $this->callOpenAIAPI("https://api.openai.com/v1/threads/{$thread_id}/messages", 'GET');
            if (isset($messages_response['error'])) {
                throw new Exception('Error al recuperar mensajes del hilo: ' . $messages_response['error']['message']);
            }

            foreach ($messages_response['data'] as $message) {
                if ($message['role'] === 'assistant' && !empty($message['content'])) {
                    foreach ($message['content'] as $content_block) {
                        if ($content_block['type'] === 'text') {
                            $ai_text_response = $content_block['text']['value'];
                            break 2;
                        }
                    }
                }
            }
            $ai_text_response = preg_replace('/^```html\s*/i', '', $ai_text_response);
            $ai_text_response = preg_replace('/\s*```$/', '', $ai_text_response);
        } catch (Exception $e) {
            error_log("Error en la interacción con OpenAI API: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al comunicarse con la IA: ' . $e->getMessage()], 500);
        }

        try {
            $planeacion_id = $this->planeacionModel->create(
                $user_id,
                $validatedData['curso_id'],
                null,
                json_encode($validatedData),
                $ai_text_response
            );

            if ($user_role === 'docente') {
                $this->userModel->consumeCredit($user_id);
                $_SESSION['user_credits'] = ($user['creditos'] ?? 0) - 1;
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Planeación generada y guardada con éxito.',
                'planeacion_id' => $planeacion_id,
                'data' => $ai_text_response
            ]);

        } catch (Exception $e) {
            error_log("Error al guardar en la base de datos o consumir crédito: " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Error interno al guardar la planeación o consumir crédito. Detalles: ' . $e->getMessage()], 500);
        }
    }
}
