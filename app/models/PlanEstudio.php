<?php
// app/models/PlanEstudio.php

class PlanEstudio {
    private $conn;
    private $table = 'planes_estudio';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo plan de estudio.
     * @param int $id_pais ID del país al que pertenece el plan.
     * @param string $nombre_plan Nombre del plan de estudio.
     * @param string|null $descripcion Descripción del plan.
     * @param int $anio_vigencia_inicio Año de inicio de vigencia.
     * @param int|null $anio_vigencia_fin Año de fin de vigencia (NULL si sigue vigente).
     * @param bool $activo Estado activo del plan.
     * @return int|false El ID del nuevo plan si es exitoso, o false si falla.
     */
    public function create($id_pais, $nombre_plan, $descripcion = null, $anio_vigencia_inicio, $anio_vigencia_fin = null, $activo = true) {
        $query = "INSERT INTO " . $this->table . " (id_pais, nombre_plan, descripcion, anio_vigencia_inicio, anio_vigencia_fin, activo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear plan de estudio: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("issiii", $id_pais, $nombre_plan, $descripcion, $anio_vigencia_inicio, $anio_vigencia_fin, $activo);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear plan de estudio: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un plan de estudio por su ID.
     * @param int $id ID del plan de estudio.
     * @return array|null Los datos del plan si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT pe.*, p.nombre as nombre_pais FROM " . $this->table . " pe JOIN paises p ON pe.id_pais = p.id WHERE pe.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener plan de estudio por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los planes de estudio.
     * @param int|null $id_pais Si se especifica, filtra por país.
     * @param bool $soloActivos Si es true, solo devuelve planes activos.
     * @return array Un array de todos los planes de estudio.
     */
    public function getAll($id_pais = null, $soloActivos = false) {
        $query = "SELECT pe.*, p.nombre as nombre_pais FROM " . $this->table . " pe JOIN paises p ON pe.id_pais = p.id";
        $conditions = [];
        $params = [];
        $types = "";

        if ($id_pais !== null) {
            $conditions[] = "pe.id_pais = ?";
            $params[] = $id_pais;
            $types .= "i";
        }
        if ($soloActivos) {
            $conditions[] = "pe.activo = 1";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " ORDER BY pe.nombre_plan ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener planes de estudio: " . $this->conn->error);
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
     * Actualiza un plan de estudio existente.
     * @param int $id ID del plan.
     * @param int $id_pais Nuevo ID del país.
     * @param string $nombre_plan Nuevo nombre del plan.
     * @param string|null $descripcion Nueva descripción.
     * @param int $anio_vigencia_inicio Nuevo año de inicio de vigencia.
     * @param int|null $anio_vigencia_fin Nuevo año de fin de vigencia.
     * @param bool $activo Nuevo estado activo.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_pais, $nombre_plan, $descripcion, $anio_vigencia_inicio, $anio_vigencia_fin, $activo) {
        $query = "UPDATE " . $this->table . " SET id_pais = ?, nombre_plan = ?, descripcion = ?, anio_vigencia_inicio = ?, anio_vigencia_fin = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar plan de estudio: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("issiiis", $id_pais, $nombre_plan, $descripcion, $anio_vigencia_inicio, $anio_vigencia_fin, $activo, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar plan de estudio: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un plan de estudio por su ID.
     * @param int $id ID del plan a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar plan de estudio: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar plan de estudio: " . $stmt->error);
        return false;
    }
}
