<?php
// app/models/Asignatura.php (Fase 1)

class Asignatura {
    private $conn;
    private $table = 'asignaturas';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea una nueva asignatura.
     * @param string $nombre Nombre de la asignatura.
     * @param bool $activo Estado activo de la asignatura.
     * @return int|false El ID de la nueva asignatura si es exitoso, o false si falla.
     */
    public function create($nombre, $activo = true) {
        $query = "INSERT INTO " . $this->table . " (nombre, activo) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear asignatura: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $nombre, $activo);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear asignatura: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene una asignatura por su ID.
     * @param int $id ID de la asignatura.
     * @return array|null Los datos de la asignatura si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener asignatura por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todas las asignaturas.
     * @param bool $soloActivas Si es true, solo devuelve asignaturas activas.
     * @return array Un array de todas las asignaturas.
     */
    public function getAll($soloActivas = false) {
        $query = "SELECT * FROM " . $this->table;
        if ($soloActivas) {
            $query .= " WHERE activo = 1";
        }
        $query .= " ORDER BY nombre ASC";
        $result = $this->conn->query($query);
        if ($result === false) {
            error_log("Error al ejecutar la consulta para obtener todas las asignaturas: " . $this->conn->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza una asignatura existente.
     * @param int $id ID de la asignatura.
     * @param string $nombre Nuevo nombre.
     * @param bool $activo Nuevo estado activo.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $nombre, $activo) {
        $query = "UPDATE " . $this->table . " SET nombre = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar asignatura: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sii", $nombre, $activo, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar asignatura: " . $stmt->error);
        return false;
    }

    /**
     * Elimina una asignatura por su ID.
     * @param int $id ID de la asignatura a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar asignatura: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar asignatura: " . $stmt->error);
        return false;
    }
}
