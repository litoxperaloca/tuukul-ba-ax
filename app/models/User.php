<?php
// app/models/User.php (Fase 1)

class User {
    private $conn;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registra un nuevo usuario.
     * Se inicializa con 1 crédito para la primera planificación gratuita.
     * @param string $nombre Nombre del usuario.
     * @param string $email Correo electrónico del usuario.
     * @param string $password Contraseña en texto plano.
     * @param string $role Rol del usuario ('docente', 'admin', 'estudiante').
     * @return bool True si el registro es exitoso, false si falla.
     */
    public function register($nombre, $email, $password, $role = 'docente') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Asignar 1 crédito inicial a los nuevos docentes para la primera planificación gratuita
        $initial_credits = ($role === 'docente') ? 1 : 0;

          $query = "INSERT INTO " . $this->table . " (nombre, email, password, role, creditos,
                                                apellidos, documento_dni, id_pais, fecha_nacimiento,
                                                genero, telefono_movil, foto_perfil_url,
                                                recibir_email_notificaciones, recibir_sms_notificaciones,
                                                recibir_novedades_promociones)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta de registro de usuario: " . $this->conn->error);
            return false;
        }

        // Valores por defecto para los nuevos campos en el registro inicial
        $apellidos = null;
        $documento_dni = null;
        $id_pais = null;
        $fecha_nacimiento = null;
        $genero = null;
        $telefono_movil = null;
        $foto_perfil_url = null;
        $recibir_email_notificaciones = 1; // Usar 1 o 0 para booleanos en MySQL
        $recibir_sms_notificaciones = 0; // Usar 1 o 0
        $recibir_novedades_promociones = 1; // Usar 1 o 0

        // CORRECCIÓN CLAVE: Cambiado "sssiisssssiss" a "sssiisssssiii"
        // Las últimas tres 's' (string) se cambiaron a 'i' (integer)
        $stmt->bind_param("ssssisssssssiii", // 15 parámetros: s,s,s,i,i,s,s,i,s,s,s,s,i,i,i
            $nombre, $email, $hashed_password, $role, $initial_credits,
            $apellidos, $documento_dni, $id_pais, $fecha_nacimiento,
            $genero, $telefono_movil, $foto_perfil_url,
            $recibir_email_notificaciones, $recibir_sms_notificaciones,
            $recibir_novedades_promociones
        );

        if ($stmt->execute()) {
            return true;
        }
        error_log("Error al ejecutar la consulta de registro de usuario: " . $stmt->error);
        return false;
    }

    /**
     * Busca un usuario por su correo electrónico.
     * @param string $email Correo electrónico a buscar.
     * @return array|null Los datos del usuario si se encuentra, o null si no.
     */
    public function findByEmail($email) {
        $query = "SELECT u.*, p.nombre as nombre_pais FROM " . $this->table . " u LEFT JOIN paises p ON u.id_pais = p.id WHERE u.email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta findByEmail: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene un usuario por su ID.
     * @param int $id ID del usuario.
     * @return array|null Los datos del usuario si se encuentra, o null si no.
     */
    public function getById($id) {
        $query = "SELECT u.*, p.nombre as nombre_pais FROM " . $this->table . " u LEFT JOIN paises p ON u.id_pais = p.id WHERE u.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta getById: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los usuarios.
     * @return array Un array de todos los usuarios.
     */
    public function getAll() {
        $query = "SELECT id, nombre, apellidos, email, role, creditos, fecha_registro FROM " . $this->table . " ORDER BY fecha_registro DESC";
        $result = $this->conn->query($query);
        if ($result === false) {
            error_log("Error al ejecutar la consulta getAll de usuarios: " . $this->conn->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Actualiza los datos personales de un usuario.
     * @param int $id ID del usuario.
     * @param string $nombre Nombre.
     * @param string $apellidos Apellidos.
     * @param string|null $documento_dni DNI.
     * @param int|null $id_pais ID del país.
     * @param string|null $fecha_nacimiento Fecha de nacimiento (YYYY-MM-DD).
     * @param string|null $genero Género.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function updatePersonalData($id, $nombre, $apellidos, $documento_dni, $id_pais, $fecha_nacimiento, $genero) {
        $query = "UPDATE " . $this->table . " SET nombre = ?, apellidos = ?, documento_dni = ?, id_pais = ?, fecha_nacimiento = ?, genero = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar updatePersonalData: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sssisss", $nombre, $apellidos, $documento_dni, $id_pais, $fecha_nacimiento, $genero, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar updatePersonalData: " . $stmt->error);
        return false;
    }

    /**
     * Actualiza los datos de cuenta de un usuario.
     * @param int $id ID del usuario.
     * @param string $email Nuevo email.
     * @param string|null $telefono_movil Teléfono móvil.
     * @param string|null $new_password Nueva contraseña (en texto plano, se hasheará).
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function updateAccountData($id, $email, $telefono_movil, $new_password = null) {
        $fields = ['email = ?', 'telefono_movil = ?'];
        $types = "ss";
        $params = [$email, $telefono_movil];

        if ($new_password !== null && !empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $fields[] = 'password = ?';
            $types .= "s";
            $params[] = $hashed_password;
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = ?";
        $types .= "i";
        $params[] = $id;

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar updateAccountData: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar updateAccountData: " . $stmt->error);
        return false;
    }

    /**
     * Actualiza la URL de la foto de perfil de un usuario.
     * @param int $id ID del usuario.
     * @param string|null $foto_perfil_url URL de la foto de perfil.
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function updateProfilePicture($id, $foto_perfil_url) {
        $query = "UPDATE " . $this->table . " SET foto_perfil_url = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar updateProfilePicture: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $foto_perfil_url, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar updateProfilePicture: " . $stmt->error);
        return false;
    }

    /**
     * Actualiza las configuraciones de notificación de un usuario.
     * @param int $id ID del usuario.
     * @param bool $recibir_email_notificaciones
     * @param bool $recibir_sms_notificaciones
     * @param bool $recibir_novedades_promociones
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function updateNotificationSettings($id, $recibir_email_notificaciones, $recibir_sms_notificaciones, $recibir_novedades_promociones) {
        $query = "UPDATE " . $this->table . " SET recibir_email_notificaciones = ?, recibir_sms_notificaciones = ?, recibir_novedades_promociones = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar updateNotificationSettings: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iiii", $recibir_email_notificaciones, $recibir_sms_notificaciones, $recibir_novedades_promociones, $id);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar updateNotificationSettings: " . $stmt->error);
        return false;
    }

    /**
     * Actualiza los créditos de un usuario.
     * @param int $userId ID del usuario.
     * @param int $amount Cantidad de créditos a añadir (positivo) o restar (negativo).
     * @return bool True si la actualización es exitosa, false si falla.
     */
    public function updateCredits($userId, $amount) {
        $query = "UPDATE " . $this->table . " SET creditos = creditos + ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            error_log("Error al preparar la consulta updateCredits: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $amount, $userId);
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        error_log("Error al ejecutar la consulta updateCredits: " . $stmt->error);
        return false;
    }

    /**
     * Consume un crédito de un usuario.
     * @param int $userId ID del usuario.
     * @return bool True si el crédito fue consumido exitosamente, false si no hay créditos o falla.
     */
    public function consumeCredit($userId) {
        // Usar una transacción para asegurar atomicidad
        $this->conn->begin_transaction();
        try {
            // Verificar créditos actuales
            $user = $this->getById($userId);
            if (!$user || $user['creditos'] <= 0) {
                $this->conn->rollback();
                return false; // No hay créditos disponibles
            }

            // Restar un crédito
            $query = "UPDATE " . $this->table . " SET creditos = creditos - 1 WHERE id = ? AND creditos > 0";
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta consumeCredit: " . $this->conn->error);
            }
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                return false; // No se pudo consumir (ej. otro proceso ya consumió el último crédito)
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en consumeCredit: " . $e->getMessage());
            return false;
        }
    }
}
