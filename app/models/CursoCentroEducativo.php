<?php
// app/models/CursoCentroEducativo.php

class CursoCentroEducativo {
    private $conn;
    private $table = 'curso_centro_educativo';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Asocia un curso a un centro educativo.
     * @param int $id_curso ID del curso.
     * @param int $id_centro_educativo ID del centro educativo.
     * @return bool True si la asociación es exitosa, false si falla (ej. ya existe).
     */
    public function create($id_curso, $id_centro_educativo) {
        $query = "INSERT INTO " . $this->table . " (id_curso, id_centro_educativo) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para asociar curso-centro: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $id_curso, $id_centro_educativo);
        // Usamos execute() y verificamos el resultado. Si la clave primaria compuesta ya existe,
        // esto fallará, lo cual es el comportamiento esperado para evitar duplicados.
        if ($stmt->execute()) {
            return true;
        }
        // Log el error si no es una duplicación (ej. error de FK, etc.)
        if ($stmt->errno !== 1062) { // 1062 es el código de error para entrada duplicada
            error_log("Error al ejecutar la consulta para asociar curso-centro: " . $stmt->error);
        }
        return false;
    }

    /**
     * Obtiene todos los centros educativos asociados a un curso.
     * @param int $id_curso ID del curso.
     * @return array Un array de centros asociados.
     */
    public function getCentrosByCursoId($id_curso) {
        $query = "SELECT cce.id_centro_educativo, ce.nombre as nombre_centro, ce.cct, p.nombre as nombre_pais
                  FROM " . $this->table . " cce
                  JOIN centros_educativos ce ON cce.id_centro_educativo = ce.id
                  JOIN paises p ON ce.id_pais = p.id
                  WHERE cce.id_curso = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener centros por curso: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todos los cursos asociados a un centro educativo.
     * @param int $id_centro_educativo ID del centro educativo.
     * @return array Un array de cursos asociados.
     */
    public function getCursosByCentroEducativoId($id_centro_educativo) {
        $query = "SELECT cce.id_curso, c.nivel, c.grado, c.asignatura, pe.nombre_plan
                  FROM " . $this->table . " cce
                  JOIN cursos c ON cce.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  WHERE cce.id_centro_educativo = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener cursos por centro: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $id_centro_educativo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Elimina la asociación entre un curso y un centro educativo.
     * @param int $id_curso ID del curso.
     * @param int $id_centro_educativo ID del centro educativo.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id_curso, $id_centro_educativo) {
        $query = "DELETE FROM " . $this->table . " WHERE id_curso = ? AND id_centro_educativo = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar asociación curso-centro: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $id_curso, $id_centro_educativo);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar asociación curso-centro: " . $stmt->error);
        return false;
    }
}
