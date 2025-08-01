<?php
// app/models/Curso.php

class Curso {
    private $conn;
    private $table = 'cursos';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo curso.
     * @param int $id_plan_estudio ID del plan de estudio al que pertenece el curso.
     * @param string $nivel Nivel educativo (ej. Preescolar, Primaria).
     * @param string $grado Grado (ej. 1°, 2°).
     * @param string $asignatura Asignatura (ej. Educación Física).
     * @param string|null $fase Fase curricular (opcional, ej. Fase 2).
     * @param string|null $descripcion Descripción del curso (opcional).
     * @param bool $activo Estado activo del curso.
     * @return int|false El ID del nuevo curso si es exitoso, o false si falla.
     */
    public function create($id_plan_estudio, $nivel, $grado, $asignatura, $fase = null, $descripcion = null, $activo = true) {
        $query = "INSERT INTO " . $this->table . " (id_plan_estudio, nivel, grado, asignatura, fase, descripcion, activo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear curso: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("issssii", $id_plan_estudio, $nivel, $grado, $asignatura, $fase, $descripcion, $activo);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear curso: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un curso por su ID.
     * @param int $id ID del curso.
     * @return array|null Los datos del curso si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT c.*, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " c
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  WHERE c.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener curso por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los cursos.
     * @param int|null $id_plan_estudio Si se especifica, filtra por plan de estudio.
     * @param bool $soloActivos Si es true, solo devuelve cursos activos.
     * @return array Un array de todos los cursos.
     */
    public function getAll($id_plan_estudio = null, $soloActivos = false) {
        $query = "SELECT c.*, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " c
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id";
        $conditions = [];
        $params = [];
        $types = "";

        if ($id_plan_estudio !== null) {
            $conditions[] = "c.id_plan_estudio = ?";
            $params[] = $id_plan_estudio;
            $types .= "i";
        }
        if ($soloActivos) {
            $conditions[] = "c.activo = 1";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " ORDER BY c.nivel, c.grado, c.asignatura ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener cursos: " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza un curso existente.
     * @param int $id ID del curso.
     * @param int $id_plan_estudio Nuevo ID del plan de estudio.
     * @param string $nivel Nuevo nivel.
     * @param string $grado Nuevo grado.
     * @param string $asignatura Nueva asignatura.
     * @param string|null $fase Nueva fase.
     * @param string|null $descripcion Nueva descripción.
     * @param bool $activo Nuevo estado activo.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_plan_estudio, $nivel, $grado, $asignatura, $fase, $descripcion, $activo) {
        $query = "UPDATE " . $this->table . " SET id_plan_estudio = ?, nivel = ?, grado = ?, asignatura = ?, fase = ?, descripcion = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar curso: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("issssiii", $id_plan_estudio, $nivel, $grado, $asignatura, $fase, $descripcion, $activo, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar curso: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un curso por su ID.
     * @param int $id ID del curso a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar curso: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar curso: " . $stmt->error);
        return false;
    }
}
