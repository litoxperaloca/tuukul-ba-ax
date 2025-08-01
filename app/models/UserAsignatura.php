<?php
// app/models/UserAsignatura.php (Fase 1)

class UserAsignatura {
    private $conn;
    private $table = 'user_asignaturas';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Asocia una asignatura a un usuario.
     * @param int $user_id ID del usuario.
     * @param int $asignatura_id ID de la asignatura.
     * @return bool True si la asociación es exitosa, false si falla (ej. ya existe).
     */
    public function create($user_id, $asignatura_id) {
        $query = "INSERT INTO " . $this->table . " (user_id, asignatura_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para asociar usuario-asignatura: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $user_id, $asignatura_id);
        if ($stmt->execute()) {
            return true;
        }
        # Log the error if it's not a duplicate entry (1062 is the error code for duplicate entry)
        if ($stmt->errno !== 1062) {
            error_log("Error al ejecutar la consulta para asociar usuario-asignatura: " . $stmt->error);
        }
        return false;
    }

    /**
     * Obtiene todas las asignaturas asociadas a un usuario.
     * @param int $user_id ID del usuario.
     * @return array Un array de asignaturas asociadas.
     */
    public function getAsignaturasByUserId($user_id) {
        $query = "SELECT ua.asignatura_id, a.nombre, a.activo
                  FROM " . $this->table . " ua
                  JOIN asignaturas a ON ua.asignatura_id = a.id
                  WHERE ua.user_id = ?
                  ORDER BY a.nombre ASC";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener asignaturas por usuario: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Elimina la asociación de una asignatura a un usuario.
     * @param int $user_id ID del usuario.
     * @param int $asignatura_id ID de la asignatura.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($user_id, $asignatura_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = ? AND asignatura_id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar asociación usuario-asignatura: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $user_id, $asignatura_id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar asociación usuario-asignatura: " . $stmt->error);
        return false;
    }
}
