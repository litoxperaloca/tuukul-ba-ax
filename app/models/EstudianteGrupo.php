<?php
// app/models/EstudianteGrupo.php

class EstudianteGrupo {
    private $conn;
    private $table = 'estudiante_grupo';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Asigna un estudiante a un grupo con observaciones de inclusión.
     * @param int $id_estudiante ID del estudiante.
     * @param int $id_grupo ID del grupo.
     * @param string|null $observaciones_inclusion Notas específicas de inclusión (opcional).
     * @return bool True si la asignación es exitosa, false si falla.
     */
    public function create($id_estudiante, $id_grupo, $observaciones_inclusion = null) {
        $query = "INSERT INTO " . $this->table . " (id_estudiante, id_grupo, observaciones_inclusion) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para asignar estudiante a grupo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iis", $id_estudiante, $id_grupo, $observaciones_inclusion);
        if ($stmt->execute()) {
            return true;
        }
        error_log("Error al ejecutar la consulta para asignar estudiante a grupo: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene todos los estudiantes de un grupo, incluyendo sus observaciones de inclusión.
     * @param int $id_grupo ID del grupo.
     * @return array Un array de estudiantes en el grupo.
     */
    public function getEstudiantesByGrupoId($id_grupo) {
        $query = "SELECT eg.*, u.nombre as nombre_estudiante, u.email as email_estudiante
                  FROM " . $this->table . " eg
                  JOIN usuarios u ON eg.id_estudiante = u.id
                  WHERE eg.id_grupo = ? AND u.role = 'estudiante'
                  ORDER BY u.nombre ASC";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener estudiantes por grupo: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los grupos a los que pertenece un estudiante.
     * @param int $id_estudiante ID del estudiante.
     * @return array Un array de grupos a los que pertenece el estudiante.
     */
    public function getGruposByEstudianteId($id_estudiante) {
        $query = "SELECT eg.*, g.nombre_grupo, g.id_docente, c.nivel, c.grado, c.asignatura
                  FROM " . $this->table . " eg
                  JOIN grupos g ON eg.id_grupo = g.id
                  JOIN cursos c ON g.id_curso = c.id
                  WHERE eg.id_estudiante = ?
                  ORDER BY g.nombre_grupo ASC";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener grupos por estudiante: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $id_estudiante);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza las observaciones de inclusión para un estudiante en un grupo.
     * @param int $id_estudiante ID del estudiante.
     * @param int $id_grupo ID del grupo.
     * @param string|null $observaciones_inclusion Nuevas observaciones.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function updateObservacionesInclusion($id_estudiante, $id_grupo, $observaciones_inclusion) {
        $query = "UPDATE " . $this->table . " SET observaciones_inclusion = ? WHERE id_estudiante = ? AND id_grupo = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar observaciones de inclusión: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sii", $observaciones_inclusion, $id_estudiante, $id_grupo);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar observaciones de inclusión: " . $stmt->error);
        return false;
    }

    /**
     * Elimina la asignación de un estudiante a un grupo.
     * @param int $id_estudiante ID del estudiante.
     * @param int $id_grupo ID del grupo.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id_estudiante, $id_grupo) {
        $query = "DELETE FROM " . $this->table . " WHERE id_estudiante = ? AND id_grupo = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar asignación estudiante-grupo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $id_estudiante, $id_grupo);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar asignación estudiante-grupo: " . $stmt->error);
        return false;
    }
}
