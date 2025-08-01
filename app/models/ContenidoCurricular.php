<?php
// app/models/ContenidoCurricular.php (Actualizado)

class ContenidoCurricular {
    private $conn;
    private $table = 'contenidos_curriculares';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo contenido curricular o eje articulador.
     * @param int $id_curso ID del curso al que pertenece.
     * @param string $nombre_contenido Nombre del contenido/eje.
     * @param string|null $pda_descripcion Descripción del PDA (opcional).
     * @param string $tipo Tipo ('contenido' o 'eje_articulador').
     * @param int $orden Orden de visualización.
     * @param bool $activo Estado activo.
     * @return int|false El ID del nuevo registro si es exitoso, o false si falla.
     */
    public function create($id_curso, $nombre_contenido, $pda_descripcion = null, $tipo, $orden = 0, $activo = true) {
        $query = "INSERT INTO " . $this->table . " (id_curso, nombre_contenido, pda_descripcion, tipo, orden, activo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para crear contenido curricular: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isssii", $id_curso, $nombre_contenido, $pda_descripcion, $tipo, $orden, $activo);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        error_log("Error al ejecutar la consulta para crear contenido curricular: " . $stmt->error);
        return false;
    }

    /**
     * Obtiene un contenido curricular por su ID.
     * @param int $id ID del contenido.
     * @return array|null Los datos del contenido si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT cc.*, c.nivel, c.grado, c.asignatura, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " cc
                  JOIN cursos c ON cc.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  WHERE cc.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener contenido curricular por ID: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los contenidos curriculares o ejes articuladores para un curso.
     * @param int $id_curso ID del curso.
     * @param string|null $tipo Si se especifica, filtra por tipo ('contenido' o 'eje_articulador').
     * @param bool $soloActivos Si es true, solo devuelve elementos activos.
     * @return array Un array de contenidos curriculares.
     */
    public function getByCursoId($id_curso, $tipo = null, $soloActivos = false) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_curso = ?";
        $params = [$id_curso];
        $types = "i";

        if ($tipo !== null) {
            $query .= " AND tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }
        if ($soloActivos) {
            $query .= " AND activo = 1";
        }
        $query .= " ORDER BY orden ASC, nombre_contenido ASC";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para obtener contenidos por curso: " . $this->conn->error);
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todos los contenidos curriculares y ejes articuladores con información del curso asociado.
     * @return array Un array de todos los contenidos curriculares.
     */
    public function getAllWithCursoInfo() {
        $query = "SELECT cc.*, c.nivel, c.grado, c.asignatura, pe.nombre_plan, p.nombre as nombre_pais
                  FROM " . $this->table . " cc
                  JOIN cursos c ON cc.id_curso = c.id
                  JOIN planes_estudio pe ON c.id_plan_estudio = pe.id
                  JOIN paises p ON pe.id_pais = p.id
                  ORDER BY c.nivel, c.grado, c.asignatura, cc.tipo, cc.orden ASC";
        $result = $this->conn->query($query);
        if ($result === false) {
            error_log("Error al ejecutar la consulta para obtener todos los contenidos con info de curso: " . $this->conn->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza un contenido curricular existente.
     * @param int $id ID del contenido.
     * @param int $id_curso Nuevo ID del curso.
     * @param string $nombre_contenido Nuevo nombre.
     * @param string|null $pda_descripcion Nueva descripción del PDA.
     * @param string $tipo Nuevo tipo.
     * @param int $orden Nuevo orden.
     * @param bool $activo Nuevo estado activo.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function update($id, $id_curso, $nombre_contenido, $pda_descripcion, $tipo, $orden, $activo) {
        $query = "UPDATE " . $this->table . " SET id_curso = ?, nombre_contenido = ?, pda_descripcion = ?, tipo = ?, orden = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para actualizar contenido curricular: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isssiii", $id_curso, $nombre_contenido, $pda_descripcion, $tipo, $orden, $activo, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para actualizar contenido curricular: " . $stmt->error);
        return false;
    }

    /**
     * Elimina un contenido curricular por su ID.
     * @param int $id ID del contenido a eliminar.
     * @return bool True si la eliminación es exitosa, false si falla.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta para eliminar contenido curricular: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta para eliminar contenido curricular: " . $stmt->error);
        return false;
    }
}
