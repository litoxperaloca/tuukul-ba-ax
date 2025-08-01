<?php
// app/models/CentroEducativo.php

class CentroEducativo {
    private $conn;
    private $table = 'centros_educativos';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo centro educativo.
     * @param int $id_pais ID del país al que pertenece el centro.
     * @param string $nombre Nombre del centro educativo.
     * @param string|null $cct Clave de Centro de Trabajo (opcional).
     * @param string|null $direccion Dirección del centro (opcional).
     * @param bool $activo Estado activo del centro.
     * @return int|false El ID del nuevo centro si es exitoso, o false si falla.
     */
    public function create($id_pais, $nombre, $cct = null, $direccion = null, $activo = true) {
        $query = "INSERT INTO " . $this->table . " (id_pais, nombre, cct, direccion, activo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear centro educativo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isssi", $id_pais, $nombre, $cct, $direccion, $activo);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear centro educativo: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un centro educativo por su ID.
     * @param int $id ID del centro educativo.
     * @return array|null Los datos del centro si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT ce.*, p.nombre as nombre_pais FROM " . $this->table . " ce JOIN paises p ON ce.id_pais = p.id WHERE ce.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener centro educativo por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los centros educativos.
     * @param int|null $id_pais Si se especifica, filtra por país.
     * @param bool $soloActivos Si es true, solo devuelve centros activos.
     * @return array Un array de todos los centros educativos.
     */
    public function getAll($id_pais = null, $soloActivos = false) {
        $query = "SELECT ce.*, p.nombre as nombre_pais FROM " . $this->table . " ce JOIN paises p ON ce.id_pais = p.id";
        $conditions = [];
        $params = [];
        $types = "";

        if ($id_pais !== null) {
            $conditions[] = "ce.id_pais = ?";
            $params[] = $id_pais;
            $types .= "i";
        }
        if ($soloActivos) {
            $conditions[] = "ce.activo = 1";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " ORDER BY ce.nombre ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener centros educativos: " . $this->conn->error);
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
     * Actualiza un centro educativo existente.
     * @param int $id ID del centro educativo.
     * @param int $id_pais Nuevo ID del país.
     * @param string $nombre Nuevo nombre.
     * @param string|null $cct Nueva Clave de Centro de Trabajo (opcional).
     * @param string|null $direccion Nueva dirección (opcional).
     * @param bool $activo Nuevo estado activo.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_pais, $nombre, $cct, $direccion, $activo) {
        $query = "UPDATE " . $this->table . " SET id_pais = ?, nombre = ?, cct = ?, direccion = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar centro educativo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isssii", $id_pais, $nombre, $cct, $direccion, $activo, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar centro educativo: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un centro educativo por su ID.
     * @param int $id ID del centro educativo a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar centro educativo: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar centro educativo: " . $stmt->error);
        return false;
    }
}
