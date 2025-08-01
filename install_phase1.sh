#!/bin/bash

# ==============================================================================
# Script de Instalación - Fase 1: Actualizaciones de BD y Modelos
# ==============================================================================
# Este script actualiza el esquema de la base de datos y los archivos de modelos
# para la Fase 1 del proyecto PlaneaIA.
#
# USO:
# 1. Guarda este archivo como `install_phase1.sh` en el directorio raíz del proyecto.
# 2. Dale permisos de ejecución: chmod +x install_phase1.sh
# 3. Ejecútalo: ./install_phase1.sh
#

echo_color() {
    local color_code=$1
    shift
    echo -e "\033[${color_code}m$@\033[0m"
}

clear
echo_color "1;34" "======================================================"
echo_color "1;34" "==   Instalador PlaneaIA - Fase 1: BD y Modelos   =="
echo_color "1;34" "======================================================"
echo ""
echo_color "1;33" "Este script actualizará el esquema de la base de datos y los modelos PHP."
echo_color "1;31" "¡ADVERTENCIA! Se eliminarán y recrearán los archivos de modelos modificados."
read -p "¿Estás listo para continuar? (s/n): " confirm
if [[ "$confirm" != "s" && "$confirm" != "S" ]]; then
    echo_color "1;31" "Instalación de Fase 1 cancelada."
    exit 1
fi

echo ""
echo_color "1;36" "--- Configuración de la Base de Datos ---"
read -p "Nombre de la Base de Datos (ej. planeacion_db2): " DB_NAME
read -p "Usuario de la Base de Datos: " DB_USER
read -s -p "Contraseña de la Base de Datos: " DB_PASS
echo ""

# --- Paso 1: Ejecutar SQL para actualizar la Base de Datos ---
echo_color "1;32" "-> Ejecutando SQL para actualizar el esquema de la base de datos..."
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" << EOF_SQL || { echo_color "1;31" "Error al ejecutar el script SQL de la base de datos. Abortando."; exit 1; }
-- Archivo: config/database.sql (Fase 1)
-- Este script crea o actualiza el esquema de la base de datos para la plataforma Planeación Educativa IA,
-- incorporando todas las funcionalidades discutidas.
--
-- NOTA IMPORTANTE: La base de datos utilizada es 'planeacion_db2'.

-- -----------------------------------------------------
-- Esquema de la Base de Datos
-- -----------------------------------------------------
CREATE DATABASE IF NOT EXISTS \`planeacion_db2\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE \`planeacion_db2\`;

-- -----------------------------------------------------
-- Tabla \`usuarios\`
-- Modificada para incluir el rol 'estudiante', la columna 'creditos',
-- y nuevos campos de perfil.
-- -----------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS UpdateUsersTable_Phase1_planeacion_db2$$
CREATE PROCEDURE UpdateUsersTable_Phase1_planeacion_db2()
BEGIN
    -- Modificar el ENUM de 'role' si es necesario
    IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios') THEN
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'role' AND column_type = "enum('docente','admin','estudiante')") THEN
            ALTER TABLE \`usuarios\` MODIFY COLUMN \`role\` ENUM('docente','admin','estudiante') NOT NULL DEFAULT 'docente';
        END IF;

        -- Añadir la columna 'creditos' si no existe
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'creditos') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`creditos\` INT(11) NOT NULL DEFAULT 0 AFTER \`role\`;
        END IF;

        -- Añadir nuevos campos de perfil si no existen
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'apellidos') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`apellidos\` VARCHAR(100) NULL AFTER \`nombre\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'documento_dni') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`documento_dni\` VARCHAR(50) NULL AFTER \`email\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'id_pais') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`id_pais\` INT(11) NULL AFTER \`documento_dni\`;
            -- Añadir FK si la tabla paises ya existe
            IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = 'planeacion_db2' AND table_name = 'paises') THEN
                ALTER TABLE \`usuarios\` ADD CONSTRAINT \`fk_usuarios_paises\` FOREIGN KEY (\`id_pais\`) REFERENCES \`paises\`(\`id\`) ON DELETE SET NULL ON UPDATE CASCADE;
            END IF;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'fecha_nacimiento') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`fecha_nacimiento\` DATE NULL AFTER \`id_pais\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'genero') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`genero\` ENUM('masculino', 'femenino', 'otro', 'prefiero_no_decir') NULL AFTER \`fecha_nacimiento\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'telefono_movil') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`telefono_movil\` VARCHAR(20) NULL AFTER \`genero\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'foto_perfil_url') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`foto_perfil_url\` VARCHAR(255) NULL AFTER \`telefono_movil\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'recibir_email_notificaciones') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`recibir_email_notificaciones\` BOOLEAN NOT NULL DEFAULT TRUE AFTER \`foto_perfil_url\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'recibir_sms_notificaciones') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`recibir_sms_notificaciones\` BOOLEAN NOT NULL DEFAULT FALSE AFTER \`recibir_email_notificaciones\`;
        END IF;
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'recibir_novedades_promociones') THEN
            ALTER TABLE \`usuarios\` ADD COLUMN \`recibir_novedades_promociones\` BOOLEAN NOT NULL DEFAULT TRUE AFTER \`recibir_sms_notificaciones\`;
        END IF;
    END IF;
END$$
DELIMITER ;

CALL UpdateUsersTable_Phase1_planeacion_db2();
DROP PROCEDURE IF EXISTS UpdateUsersTable_Phase1_planeacion_db2;

-- Creación de la tabla \`usuarios\` si no existe (con los campos ya actualizados)
CREATE TABLE IF NOT EXISTS \`usuarios\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`nombre\` VARCHAR(100) NOT NULL,
  \`apellidos\` VARCHAR(100) NULL,
  \`email\` VARCHAR(100) NOT NULL UNIQUE,
  \`documento_dni\` VARCHAR(50) NULL,
  \`id_pais\` INT(11) NULL,
  \`fecha_nacimiento\` DATE NULL,
  \`genero\` ENUM('masculino', 'femenino', 'otro', 'prefiero_no_decir') NULL,
  \`telefono_movil\` VARCHAR(20) NULL,
  \`foto_perfil_url\` VARCHAR(255) NULL,
  \`recibir_email_notificaciones\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`recibir_sms_notificaciones\` BOOLEAN NOT NULL DEFAULT FALSE,
  \`recibir_novedades_promociones\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`password\` VARCHAR(255) NOT NULL,
  \`role\` ENUM('docente','admin','estudiante') NOT NULL DEFAULT 'docente',
  \`creditos\` INT(11) NOT NULL DEFAULT 0,
  \`fecha_registro\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  CONSTRAINT \`fk_usuarios_paises\` FOREIGN KEY (\`id_pais\`) REFERENCES \`paises\`(\`id\`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- -----------------------------------------------------
-- Tabla \`paises\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`paises\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`nombre\` VARCHAR(100) NOT NULL UNIQUE,
  \`codigo_iso\` VARCHAR(5) NOT NULL UNIQUE,
  \`activo\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`centros_educativos\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`centros_educativos\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_pais\` INT(11) NOT NULL,
  \`nombre\` VARCHAR(255) NOT NULL,
  \`cct\` VARCHAR(50) UNIQUE NULL,
  \`direccion\` TEXT,
  \`activo\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  CONSTRAINT \`fk_centros_educativos_paises\`
    FOREIGN KEY (\`id_pais\`) REFERENCES \`paises\`(\`id\`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`planes_estudio\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`planes_estudio\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_pais\` INT(11) NOT NULL,
  \`nombre_plan\` VARCHAR(255) NOT NULL,
  \`descripcion\` TEXT,
  \`anio_vigencia_inicio\` YEAR NOT NULL,
  \`anio_vigencia_fin\` YEAR NULL,
  \`activo\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`idx_plan_unique\` (\`id_pais\`, \`nombre_plan\`, \`anio_vigencia_inicio\`),
  CONSTRAINT \`fk_planes_estudio_paises\`
    FOREIGN KEY (\`id_pais\`) REFERENCES \`paises\`(\`id\`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`cursos\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`cursos\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_plan_estudio\` INT(11) NOT NULL,
  \`nivel\` VARCHAR(100) NOT NULL,
  \`grado\` VARCHAR(50) NOT NULL,
  \`asignatura\` VARCHAR(100) NOT NULL,
  \`fase\` VARCHAR(50),
  \`descripcion\` TEXT,
  \`activo\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`idx_curso_unique_in_plan\` (\`id_plan_estudio\`, \`nivel\`, \`grado\`, \`asignatura\`),
  CONSTRAINT \`fk_cursos_planes_estudio\`
    FOREIGN KEY (\`id_plan_estudio\`) REFERENCES \`planes_estudio\`(\`id\`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`curso_centro_educativo\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`curso_centro_educativo\` (
  \`id_curso\` INT(11) NOT NULL,
  \`id_centro_educativo\` INT(11) NOT NULL,
  \`fecha_asignacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id_curso\`, \`id_centro_educativo\`),
  CONSTRAINT \`fk_cce_curso\`
    FOREIGN KEY (\`id_curso\`) REFERENCES \`cursos\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT \`fk_cce_centro_educativo\`
    FOREIGN KEY (\`id_centro_educativo\`) REFERENCES \`centros_educativos\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`contenidos_curriculares\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`contenidos_curriculares\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_curso\` INT(11) NOT NULL,
  \`nombre_contenido\` VARCHAR(255) NOT NULL,
  \`pda_descripcion\` TEXT,
  \`tipo\` ENUM('contenido', 'eje_articulador') NOT NULL,
  \`orden\` INT(11) DEFAULT 0,
  \`activo\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  CONSTRAINT \`fk_contenidos_curriculares_cursos\`
    FOREIGN KEY (\`id_curso\`) REFERENCES \`cursos\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`formularios_dinamicos\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`formularios_dinamicos\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_curso\` INT(11) NOT NULL UNIQUE,
  \`nombre\` VARCHAR(255) NOT NULL,
  \`schema_json\` JSON NOT NULL,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  CONSTRAINT \`fk_formularios_dinamicos_cursos\`
    FOREIGN KEY (\`id_curso\`) REFERENCES \`cursos\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`openai_assistant_configs\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`openai_assistant_configs\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_curso\` INT(11) NOT NULL UNIQUE,
  \`assistant_id\` VARCHAR(255) NOT NULL,
  \`vector_store_id\` VARCHAR(255) NULL,
  \`instrucciones_adicionales\` TEXT,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  CONSTRAINT \`fk_openai_assistant_configs_cursos\`
    FOREIGN KEY (\`id_curso\`) REFERENCES \`cursos\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`grupos\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`grupos\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_docente\` INT(11) NOT NULL,
  \`id_curso\` INT(11) NOT NULL,
  \`nombre_grupo\` VARCHAR(100) NOT NULL,
  \`descripcion\` TEXT NULL,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`idx_grupo_docente_curso_nombre\` (\`id_docente\`, \`id_curso\`, \`nombre_grupo\`),
  CONSTRAINT \`fk_grupos_docente\`
    FOREIGN KEY (\`id_docente\`) REFERENCES \`usuarios\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT \`fk_grupos_curso\`
    FOREIGN KEY (\`id_curso\`) REFERENCES \`cursos\`(\`id\`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`estudiante_grupo\` (se mantiene la estructura)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`estudiante_grupo\` (
  \`id_estudiante\` INT(11) NOT NULL,
  \`id_grupo\` INT(11) NOT NULL,
  \`observaciones_inclusion\` TEXT NULL,
  \`fecha_asignacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id_estudiante\`, \`id_grupo\`),
  CONSTRAINT \`fk_estudiante_grupo_estudiante\`
    FOREIGN KEY (\`id_estudiante\`) REFERENCES \`usuarios\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT \`fk_estudiante_grupo_grupo\`
    FOREIGN KEY (\`id_grupo\`) REFERENCES \`grupos\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla \`planeaciones\` (se mantiene la estructura)
-- -----------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS UpdatePlaneacionesTable_planeacion_db2$$
CREATE PROCEDURE UpdatePlaneacionesTable_planeacion_db2()
BEGIN
    IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones') THEN
        -- Eliminar FK si existe
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND constraint_name = 'fk_planeaciones_cursos') THEN
            ALTER TABLE \`planeaciones\` DROP FOREIGN KEY \`fk_planeaciones_cursos\`;
        END IF;
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND constraint_name = 'fk_planeaciones_cursos_new') THEN
            ALTER TABLE \`planeaciones\` DROP FOREIGN KEY \`fk_planeaciones_cursos_new\`;
        END IF;
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND constraint_name = 'fk_planeaciones_grupos') THEN
            ALTER TABLE \`planeaciones\` DROP FOREIGN KEY \`fk_planeaciones_grupos\`;
        END IF;

        -- Eliminar columnas si existen
        IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND column_name = 'id_curso') THEN
            ALTER TABLE \`planeaciones\` DROP COLUMN \`id_curso\`;
        END IF;

        IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND column_name = 'id_grupo') THEN
            ALTER TABLE \`planeaciones\` DROP COLUMN \`id_grupo\`;
        END IF;
    END IF;
END$$
DELIMITER ;

CALL UpdatePlaneacionesTable_planeacion_db2();
DROP PROCEDURE IF EXISTS UpdatePlaneacionesTable_planeacion_db2;

-- Creación de la tabla \`planeaciones\` si no existe, o añadir columnas si ya existe.
CREATE TABLE IF NOT EXISTS \`planeaciones\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`id_usuario\` INT(11) NOT NULL,
  \`id_curso\` INT(11) NULL,
  \`id_grupo\` INT(11) NULL,
  \`prompt_data\` JSON NOT NULL,
  \`respuesta_ia\` LONGTEXT DEFAULT NULL,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`),
  KEY \`id_usuario\` (\`id_usuario\`),
  CONSTRAINT \`fk_planeaciones_usuarios\`
    FOREIGN KEY (\`id_usuario\`) REFERENCES \`usuarios\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT \`fk_planeaciones_cursos_new\`
    FOREIGN KEY (\`id_curso\`) REFERENCES \`cursos\`(\`id\`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT \`fk_planeaciones_grupos\`
    FOREIGN KEY (\`id_grupo\`) REFERENCES \`grupos\`(\`id\`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- -----------------------------------------------------
-- Nueva Tabla \`asignaturas\`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`asignaturas\` (
  \`id\` INT(11) NOT NULL AUTO_INCREMENT,
  \`nombre\` VARCHAR(100) NOT NULL UNIQUE,
  \`activo\` BOOLEAN NOT NULL DEFAULT TRUE,
  \`fecha_creacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  \`fecha_actualizacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Nueva Tabla \`user_asignaturas\` (Tabla pivote para relación muchos-a-muchos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS \`user_asignaturas\` (
  \`user_id\` INT(11) NOT NULL,
  \`asignatura_id\` INT(11) NOT NULL,
  \`fecha_asignacion\` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (\`user_id\`, \`asignatura_id\`),
  CONSTRAINT \`fk_user_asignaturas_user\`
    FOREIGN KEY (\`user_id\`) REFERENCES \`usuarios\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT \`fk_user_asignaturas_asignatura\`
    FOREIGN KEY (\`asignatura_id\`) REFERENCES \`asignaturas\`(\`id\`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
EOF_SQL
echo_color "1;32" "   SQL de base de datos ejecutado con éxito."

# --- Paso 2: Crear directorios necesarios ---
echo_color "1;32" "-> Creando directorios necesarios..."
mkdir -p app/models
echo_color "1;32" "   Directorios creados."

# --- Paso 3: Crear/Reemplazar archivos de modelos ---

# app/models/User.php
echo_color "1;32" "-> Creando/Reemplazando app/models/User.php..."
rm -f app/models/User.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_USER' > app/models/User.php
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

        // La consulta de registro ahora incluye los nuevos campos con valores por defecto/NULL
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
        $recibir_email_notificaciones = true;
        $recibir_sms_notificaciones = false;
        $recibir_novedades_promociones = true;

        $stmt->bind_param("sssiisssssiss",
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
EOF_PHP_USER

# app/models/Asignatura.php
echo_color "1;32" "-> Creando app/models/Asignatura.php..."
rm -f app/models/Asignatura.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_ASIGNATURA' > app/models/Asignatura.php
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
EOF_PHP_ASIGNATURA

# app/models/UserAsignatura.php
echo_color "1;32" "-> Creando app/models/UserAsignatura.php..."
rm -f app/models/UserAsignatura.php # Eliminar archivo existente para asegurar reemplazo
cat << 'EOF_PHP_USER_ASIGNATURA' > app/models/UserAsignatura.php
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
EOF_PHP_USER_ASIGNATURA

echo_color "1;32" "   Archivos de modelos creados/reemplazados con éxito."

echo ""
echo_color "1;32" "¡Fase 1 completada!"
echo ""
echo_color "1;33" "--- Próximos Pasos ---"
echo_color "1;37" "1.  Asegúrate de que tu `config/config.php` tenga el nombre de base de datos correcto ('planeacion_db2')."
echo_color "1;37" "2.  Si aún no lo has hecho, crea un usuario administrador con: php scripts/promote_user.php tu-email@ejemplo.com"
echo_color "1;37" "3.  Ahora puedes proceder con la Fase 2 de la implementación."
echo ""
echo_color "1;34" "======================================================"
echo_color "1;34" "==                 Fin del Script                 =="
echo_color "1;34" "======================================================"
echo ""

