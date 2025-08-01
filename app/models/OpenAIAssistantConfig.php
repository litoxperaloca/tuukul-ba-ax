<?php
// app/models/OpenAIAssistantConfig.php (Actualizado)

class OpenAIAssistantConfig {
    private $conn;
    private $table = 'openai_assistant_configs';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea una nueva configuración de asistente de OpenAI.
     * @param int $id_curso ID del curso al que se asocia el asistente.
     * @param string $assistant_id ID del asistente de OpenAI.
     * @param string|null $vector_store_id ID del Vector Store de OpenAI (opcional).
     * @param string|null $instrucciones_adicionales Instrucciones de sistema adicionales para el asistente (opcional).
     * @return int|false El ID de la nueva configuración si es exitoso, o false si falla.
     */
    public function create($id_curso, $assistant_id, $vector_store_id = null, $instrucciones_adicionales = null) {
        $query = "INSERT INTO " . $this->table . " (id_curso, assistant_id, vector_store_id, instrucciones_adicionales) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear configuración de asistente: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isss", $id_curso, $assistant_id, $vector_store_id, $instrucciones_adicionales);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear configuración de asistente: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene una configuración de asistente por su ID.
     * @param int $id ID de la configuración.
     * @return array|null Los datos de la configuración si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT oac.*, c.nivel, c.grado, c.asignatura, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " oac
                  JOIN cursos c ON oac.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  WHERE oac.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener configuración de asistente por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene una configuración de asistente por el ID de un curso.
     * @param int $id_curso ID del curso.
     * @return array|null Los datos de la configuración si se encuentra, o null si no.
     */
    public function getByCursoId($id_curso) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_curso = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener configuración de asistente por curso ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todas las configuraciones de asistentes de OpenAI con información del curso asociado.
     * @return array Un array de todas las configuraciones de asistentes.
     */
    public function getAllWithCursoInfo() {
        $query = "SELECT oac.*, c.nivel, c.grado, c.asignatura, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " oac
                  JOIN cursos c ON oac.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  ORDER BY c.nivel, c.grado, c.asignatura ASC";
        $result = $this->conn->query($query);
        if ($result === false) {
            error_log("Error al ejecutar la consulta para obtener todas las configuraciones de asistentes con info de curso: " . $this->conn->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza una configuración de asistente existente.
     * @param int $id ID de la configuración.
     * @param int $id_curso Nuevo ID del curso.
     * @param string $assistant_id Nuevo ID del asistente.
     * @param string|null $vector_store_id Nuevo ID del Vector Store.
     * @param string|null $instrucciones_adicionales Nuevas instrucciones adicionales.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_curso, $assistant_id, $vector_store_id, $instrucciones_adicionales) {
        $query = "UPDATE " . $this->table . " SET id_curso = ?, assistant_id = ?, vector_store_id = ?, instrucciones_adicionales = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar configuración de asistente: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isssi", $id_curso, $assistant_id, $vector_store_id, $instrucciones_adicionales, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar configuración de asistente: " . $stmt->error);
        return false;
    }

    /**
     * Elimina una configuración de asistente por su ID.
     * @param int $id ID de la configuración a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar configuración de asistente: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar configuración de asistente: " . $stmt->error);
        return false;
    }
}
