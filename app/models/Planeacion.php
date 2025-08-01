<?php
// Archivo: app/models/Planeacion.php (Actualizado para JOIN con cursos)

class Planeacion {
    private $conn;
    private $table_name = "planeaciones";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea una nueva planeación en la base de datos.
     * @param int $id_usuario ID del usuario que crea la planeación.
     * @param int $id_curso ID del curso asociado.
     * @param int|null $id_grupo ID del grupo asociado (puede ser null).
     * @param string $prompt_data_json Datos del prompt en formato JSON.
     * @param string $respuesta_ia Respuesta generada por la IA.
     * @return int|false El ID de la nueva planeación insertada o false en caso de error.
     * @throws Exception Si ocurre un error en la base de datos.
     */
    public function create($id_usuario, $id_curso, $id_grupo, $prompt_data_json, $respuesta_ia) {
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, id_curso, id_grupo, prompt_data, respuesta_ia, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";

        if ($stmt = $this->conn->prepare($query)) {
            // Manejo de id_grupo, si es null, se usa null en el bind_param
            // 'i' para int, 's' para string. Si id_grupo puede ser null, usar 'i' y pasar null.
            // Si id_grupo es INT(11) NULL en la DB, se puede pasar null directamente.
            $stmt->bind_param("iisss", $id_usuario, $id_curso, $id_grupo, $prompt_data_json, $respuesta_ia);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $stmt->close();
                return $new_id;
            } else {
                $error_message = "Error al ejecutar la inserción de planeación: " . $stmt->error;
                error_log($error_message);
                $stmt->close();
                throw new Exception($error_message); // Lanza una excepción para que ApiController la capture
            }
        } else {
            $error_message = "Error al preparar la consulta de inserción de planeación: " . $this->conn->error;
            error_log($error_message);
            throw new Exception($error_message); // Lanza una excepción
        }
    }

    /**
     * Obtiene una planeación por su ID y el ID de usuario, incluyendo detalles del curso.
     * @param int $id ID de la planeación.
     * @param int $user_id ID del usuario.
     * @return array|null La planeación o null si no se encuentra.
     */
    public function getByIdAndUserId($id, $user_id) {
        $query = "SELECT p.*, c.nivel, c.grado, c.asignatura, c.fase
                  FROM " . $this->table_name . " p
                  JOIN cursos c ON p.id_curso = c.id
                  WHERE p.id = ? AND p.id_usuario = ? LIMIT 1";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $planeacion = $result->fetch_assoc();
            $stmt->close();
            return $planeacion;
        } else {
            error_log("Error al preparar la consulta getByIdAndUserId: " . $this->conn->error);
            return null;
        }
    }

    /**
     * Obtiene todas las planeaciones de un usuario, incluyendo detalles del curso.
     * @param int $user_id ID del usuario.
     * @return array Lista de planeaciones.
     */
    public function getByUserIdWithCourseDetails($user_id) {
        $query = "SELECT p.*, c.nivel, c.grado, c.asignatura, c.fase
                  FROM " . $this->table_name . " p
                  JOIN cursos c ON p.id_curso = c.id
                  WHERE p.id_usuario = ?
                  ORDER BY p.fecha_creacion DESC";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $planeaciones = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $planeaciones;
        } else {
            error_log("Error al preparar la consulta getByUserIdWithCourseDetails: " . $this->conn->error);
            return [];
        }
    }

    // Puedes mantener el método getByUserId original si lo necesitas en algún otro lugar,
    // pero para el dashboard, getByUserIdWithCourseDetails es más útil.
    // public function getByUserId($user_id) {
    //     $query = "SELECT * FROM " . $this->table_name . " WHERE id_usuario = ? ORDER BY fecha_creacion DESC";
    //     if ($stmt = $this->conn->prepare($query)) {
    //         $stmt->bind_param("i", $user_id);
    //         $stmt->execute();
    //         $result = $stmt->get_result();
    //         $planeaciones = $result->fetch_all(MYSQLI_ASSOC);
    //         $stmt->close();
    //         return $planeaciones;
    //     } else {
    //         error_log("Error al preparar la consulta getByUserId: " . $this->conn->error);
    //         return [];
    //     }
    // }

    /**
     * Elimina una planeación por su ID.
     * @param int $id ID de la planeación a eliminar.
     * @return bool True si se eliminó con éxito, false en caso contrario.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }
}
