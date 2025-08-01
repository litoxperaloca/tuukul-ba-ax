<?php
// app/models/Pais.php

class Pais {
    private $conn;
    private $table = 'paises';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo país.
     * @param string $nombre Nombre del país.
     * @param string $codigo_iso Código ISO del país (ej. MX, US).
     * @param bool $activo Estado activo del país.
     * @return int|false El ID del nuevo país si es exitoso, o false si falla.
     */
    public function create($nombre, $codigo_iso, $activo = true) {
        $query = "INSERT INTO " . $this->table . " (nombre, codigo_iso, activo) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear país: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssi", $nombre, $codigo_iso, $activo);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear país: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un país por su ID.
     * @param int $id ID del país.
     * @return array|null Los datos del país si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener país por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los países.
     * @param bool $soloActivos Si es true, solo devuelve países activos.
     * @return array Un array de todos los países.
     */
    public function getAll($soloActivos = false) {
        $query = "SELECT * FROM " . $this->table;
        if ($soloActivos) {
            $query .= " WHERE activo = 1";
        }
        $query .= " ORDER BY nombre ASC";
        $result = $this->conn->query($query);
        if ($result === false) {
            error_log("Error al ejecutar la consulta para obtener todos los países: " . $this->conn->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza un país existente.
     * @param int $id ID del país.
     * @param string $nombre Nuevo nombre.
     * @param string $codigo_iso Nuevo código ISO.
     * @param bool $activo Nuevo estado activo.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $nombre, $codigo_iso, $activo) {
        $query = "UPDATE " . $this->table . " SET nombre = ?, codigo_iso = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar país: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssii", $nombre, $codigo_iso, $activo, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar país: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un país por su ID.
     * @param int $id ID del país a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar país: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar país: " . $stmt->error);
        return false;
    }
}
