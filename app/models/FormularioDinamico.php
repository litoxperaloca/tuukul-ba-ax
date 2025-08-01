<?php
// app/models/FormularioDinamico.php (Actualizado para depuración y robustez JSON)

class FormularioDinamico {
    private $conn;
    private $table = 'formularios_dinamicos';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo formulario dinámico.
     * @param int $id_curso ID del curso al que se asocia el formulario.
     * @param string $nombre Nombre del formulario.
     * @param array $schema_json Esquema JSON del formulario (como array PHP).
     * @return int|false El ID del nuevo formulario si es exitoso, o false si falla.
     */
    public function create($id_curso, $nombre, $schema_json) {
        $query = "INSERT INTO " . $this->table . " (id_curso, nombre, schema_json) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("FormularioDinamico Model: Error al preparar la consulta para crear formulario dinámico: " . $this->conn->error);
            return false;
        }
        // Codifica el array PHP a una cadena JSON. Usar flags para mejor formato.
        $json_data = json_encode($schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json_data === false) {
            error_log("FormularioDinamico Model: Error al codificar schema_json a JSON: " . json_last_error_msg());
            return false;
        }
        error_log("FormularioDinamico Model: CREATE - schema_json input (PHP array): " . print_r($schema_json, true));
        error_log("FormularioDinamico Model: CREATE - json_data encoded (string): " . $json_data);

        $stmt->bind_param("iss", $id_curso, $nombre, $json_data);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("FormularioDinamico Model: Error al ejecutar la consulta para crear formulario dinámico: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un formulario dinámico por su ID.
     * @param int $id ID del formulario.
     * @return array|null Los datos del formulario si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT fd.*, c.nivel, c.grado, c.asignatura, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " fd
                  JOIN cursos c ON fd.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  WHERE fd.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("FormularioDinamico Model: Error al preparar la consulta para obtener formulario dinámico por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $form = $result->fetch_assoc();
        if ($form) {
            error_log("FormularioDinamico Model: GET BY ID - raw schema_json from DB: " . $form['schema_json']);
            // Asegúrate de que el valor de la DB sea una cadena antes de decodificar
            if (is_string($form['schema_json'])) {
                $decoded_json = json_decode($form['schema_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("FormularioDinamico Model: Error al decodificar schema_json de DB para ID " . $id . ": " . json_last_error_msg());
                    $form['schema_json'] = ['fields' => []]; // Retorna un array vacío si hay error
                } else {
                    $form['schema_json'] = $decoded_json;
                }
            } else {
                // Si no es una cadena (ej. es NULL de la DB), asigna un array vacío
                $form['schema_json'] = ['fields' => []];
            }
            error_log("FormularioDinamico Model: GET BY ID - decoded schema_json: " . print_r($form['schema_json'], true));
        }
        return $form;
    }
    /**
     * Obtiene un formulario dinámico por el ID de un curso.
     * @param int $id_curso ID del curso.
     * @return array|null Los datos del formulario si se encuentra, o null si no.
     */
    public function getByCursoId($id_curso) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_curso = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("FormularioDinamico Model: Error al preparar la consulta para obtener formulario dinámico por curso ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $result = $stmt->get_result();
        $form = $result->fetch_assoc();
        if ($form) {
            error_log("FormularioDinamico Model: GET BY CURSO ID - raw schema_json from DB: " . $form['schema_json']);
            // Asegúrate de que el valor de la DB sea una cadena antes de decodificar
            if (is_string($form['schema_json'])) {
                $decoded_json = json_decode($form['schema_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("FormularioDinamico Model: Error al decodificar schema_json de DB para curso ID " . $id_curso . ": " . json_last_error_msg());
                    $form['schema_json'] = ['fields' => []]; // Retorna un array vacío si hay error
                } else {
                    $form['schema_json'] = $decoded_json;
                }
            } else {
                // Si no es una cadena (ej. es NULL de la DB), asigna un array vacío
                $form['schema_json'] = ['fields' => []];
            }
            error_log("FormularioDinamico Model: GET BY CURSO ID - decoded schema_json: " . print_r($form['schema_json'], true));
        }
        return $form;
    }

    /**
     * Obtiene todos los formularios dinámicos con información del curso asociado.
     * @return array Un array de todos los formularios dinámicos.
     */
    public function getAllWithCursoInfo() {
        $query = "SELECT fd.*, c.nivel, c.grado, c.asignatura, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " fd
                  JOIN cursos c ON fd.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  ORDER BY c.nivel, c.grado, c.asignatura ASC";
        $result = $this->conn->query($query);
        if ($result === false) {
            error_log("FormularioDinamico Model: Error al ejecutar la consulta para obtener todos los formularios dinámicos con info de curso: " . $this->conn->error);
            return [];
        }
        $forms = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($forms as &$form) {
            // Asegúrate de que el valor de la DB sea una cadena antes de decodificar
            if (is_string($form['schema_json'])) {
                $decoded_json = json_decode($form['schema_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("FormularioDinamico Model: Error al decodificar schema_json de DB en getAllWithCursoInfo para ID " . $form['id'] . ": " . json_last_error_msg());
                    $form['schema_json'] = ['fields' => []]; // Invalida si hay error de decodificación
                } else {
                    $form['schema_json'] = $decoded_json;
                }
            } else {
                // Si no es una cadena (ej. es NULL de la DB), asigna un array vacío
                $form['schema_json'] = ['fields' => []];
            }
        }
        unset($form); // Romper la referencia
        return $forms;
    }

    /**
     * Actualiza un formulario dinámico existente.
     * @param int $id ID del formulario.
     * @param int $id_curso Nuevo ID del curso.
     * @param string $nombre Nuevo nombre.
     * @param array $schema_json Nuevo esquema JSON (como array PHP).
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_curso, $nombre, $schema_json) {
        $query = "UPDATE " . $this->table . " SET id_curso = ?, nombre = ?, schema_json = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("FormularioDinamico Model: Error al preparar la consulta para actualizar formulario dinámico: " . $this->conn->error);
            return false;
        }
        // Codifica el array PHP a una cadena JSON. Usar flags para mejor formato.
        $json_data = json_encode($schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json_data === false) {
            error_log("FormularioDinamico Model: Error al codificar schema_json a JSON en update: " . json_last_error_msg());
            return false;
        }
        error_log("FormularioDinamico Model: UPDATE - schema_json input (PHP array): " . print_r($schema_json, true));
        error_log("FormularioDinamico Model: UPDATE - json_data encoded (string): " . $json_data);

        $stmt->bind_param("issi", $id_curso, $nombre, $json_data, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("FormularioDinamico Model: Error al ejecutar la consulta para actualizar formulario dinámico: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un formulario dinámico por su ID.
     * @param int $id ID del formulario a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("FormularioDinamico Model: Error al preparar la consulta para eliminar formulario dinámico: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("FormularioDinamico Model: Error al ejecutar la consulta para eliminar formulario dinámico: " . $stmt->error);
        return false;
    }
}
