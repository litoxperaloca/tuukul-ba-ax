<?php
// app/models/Grupo.php

class Grupo {
    private $conn;
    private $table = 'grupos';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo grupo.
     * @param int $id_docente ID del docente propietario del grupo.
     * @param int $id_curso ID del curso al que pertenece el grupo.
     * @param string $nombre_grupo Nombre del grupo.
     * @param string|null $descripcion Descripción del grupo (opcional).
     * @return int|false El ID del nuevo grupo si es exitoso, o false si falla.
     */
    public function create($id_docente, $id_curso, $nombre_grupo, $descripcion = null) {
        $query = "INSERT INTO " . $this->table . " (id_docente, id_curso, nombre_grupo, descripcion) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear grupo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iiss", $id_docente, $id_curso, $nombre_grupo, $descripcion);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear grupo: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un grupo por su ID.
     * @param int $id ID del grupo.
     * @return array|null Los datos del grupo si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT g.*, u.nombre as nombre_docente, c.nivel, c.grado, c.asignatura
                  FROM " . $this->table . " g
                  JOIN usuarios u ON g.id_docente = u.id
                  JOIN cursos c ON g.id_curso = c.id
                  WHERE g.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener grupo por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los grupos de un docente.
     * @param int $id_docente ID del docente.
     * @param int|null $id_curso Si se especifica, filtra por curso.
     * @return array Un array de todos los grupos del docente.
     */
    public function getByDocenteId($id_docente, $id_curso = null) {
        $query = "SELECT g.*, c.nivel, c.grado, c.asignatura
                  FROM " . $this->table . " g
                  JOIN cursos c ON g.id_curso = c.id
                  WHERE g.id_docente = ?";
        $params = [$id_docente];
        $types = "i";

        if ($id_curso !== null) {
            $query .= " AND g.id_curso = ?";
            $params[] = $id_curso;
            $types .= "i";
        }
        $query .= " ORDER BY g.nombre_grupo ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener grupos por docente: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza un grupo existente.
     * @param int $id ID del grupo.
     * @param int $id_docente Nuevo ID del docente propietario.
     * @param int $id_curso Nuevo ID del curso.
     * @param string $nombre_grupo Nuevo nombre del grupo.
     * @param string|null $descripcion Nueva descripción.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_docente, $id_curso, $nombre_grupo, $descripcion) {
        $query = "UPDATE " . $this->table . " SET id_docente = ?, id_curso = ?, nombre_grupo = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar grupo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iissi", $id_docente, $id_curso, $nombre_grupo, $descripcion, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar grupo: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un grupo por su ID.
     * @param int $id ID del grupo a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar grupo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar grupo: " . $stmt->error);
        return false;
    }
}
