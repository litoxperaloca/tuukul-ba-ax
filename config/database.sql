-- Archivo: config/database.sql (SCRIPT COMPLETO Y ACTUALIZADO)
-- Este script crea o actualiza el esquema de la base de datos para la plataforma Planeación Educativa IA,
-- incorporando todas las funcionalidades discutidas.
--
-- NOTA IMPORTANTE: La base de datos utilizada es 'planeacion_db2'.

-- -----------------------------------------------------
-- Esquema de la Base de Datos
-- -----------------------------------------------------
CREATE DATABASE IF NOT EXISTS `planeacion_db2` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `planeacion_db2`;

-- -----------------------------------------------------
-- Tabla `usuarios`
-- Modificada para incluir el rol 'estudiante' y la columna 'creditos'.
-- -----------------------------------------------------
-- Primero, si la tabla existe, modificamos el ENUM de 'role' y añadimos 'creditos' si no existen.
-- Esto es para hacer el script más idempotente.
DELIMITER $$
CREATE PROCEDURE AddUserColumnsAndModifyRole_planeacion_db2()
BEGIN
    IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios') THEN
        -- Modificar el ENUM de 'role' si es necesario
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'role' AND column_type = "enum('docente','admin','estudiante')") THEN
            ALTER TABLE `usuarios` MODIFY COLUMN `role` ENUM('docente','admin','estudiante') NOT NULL DEFAULT 'docente';
        END IF;

        -- Añadir la columna 'creditos' si no existe
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'usuarios' AND column_name = 'creditos') THEN
            ALTER TABLE `usuarios` ADD COLUMN `creditos` INT(11) NOT NULL DEFAULT 0 AFTER `role`;
        END IF;
    END IF;
END$$
DELIMITER ;

CALL AddUserColumnsAndModifyRole_planeacion_db2();
DROP PROCEDURE IF EXISTS AddUserColumnsAndModifyRole_planeacion_db2;

-- Creación de la tabla `usuarios` si no existe (con los campos ya actualizados)
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('docente','admin','estudiante') NOT NULL DEFAULT 'docente',
  `creditos` INT(11) NOT NULL DEFAULT 0, -- Nueva columna para el modelo de negocio
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- -----------------------------------------------------
-- Tabla `paises`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `paises` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `codigo_iso` VARCHAR(5) NOT NULL UNIQUE, -- Ej: MX, US, AR
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `centros_educativos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `centros_educativos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pais` INT(11) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `cct` VARCHAR(50) UNIQUE NULL, -- Clave de Centro de Trabajo (México), puede ser NULL para otros países
  `direccion` TEXT,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_centros_educativos_paises`
    FOREIGN KEY (`id_pais`) REFERENCES `paises`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `planes_estudio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `planes_estudio` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pais` INT(11) NOT NULL,
  `nombre_plan` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `anio_vigencia_inicio` YEAR NOT NULL,
  `anio_vigencia_fin` YEAR NULL, -- NULL si el plan sigue vigente
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_plan_unique` (`id_pais`, `nombre_plan`, `anio_vigencia_inicio`), -- Unicidad del plan por país y año de inicio
  CONSTRAINT `fk_planes_estudio_paises`
    FOREIGN KEY (`id_pais`) REFERENCES `paises`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `cursos`
-- Vinculada a `planes_estudio`.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cursos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_plan_estudio` INT(11) NOT NULL,
  `nivel` VARCHAR(100) NOT NULL, -- Ej: Preescolar, Primaria, Secundaria
  `grado` VARCHAR(50) NOT NULL, -- Ej: 1°, 2°, 3°
  `asignatura` VARCHAR(100) NOT NULL, -- Ej: Educación Física
  `fase` VARCHAR(50), -- Ej: Fase 2, Fase 3 (para Nueva Escuela Mexicana)
  `descripcion` TEXT,
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_curso_unique_in_plan` (`id_plan_estudio`, `nivel`, `grado`, `asignatura`), -- Unicidad de curso dentro de un plan
  CONSTRAINT `fk_cursos_planes_estudio`
    FOREIGN KEY (`id_plan_estudio`) REFERENCES `planes_estudio`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `curso_centro_educativo`
-- Relación muchos a muchos entre Cursos y Centros Educativos.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `curso_centro_educativo` (
  `id_curso` INT(11) NOT NULL,
  `id_centro_educativo` INT(11) NOT NULL,
  `fecha_asignacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_curso`, `id_centro_educativo`), -- Clave primaria compuesta
  CONSTRAINT `fk_cce_curso`
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cce_centro_educativo`
    FOREIGN KEY (`id_centro_educativo`) REFERENCES `centros_educativos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `contenidos_curriculares`
-- Reemplaza los datos hardcodeados.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `contenidos_curriculares` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_curso` INT(11) NOT NULL,
  `nombre_contenido` VARCHAR(255) NOT NULL,
  `pda_descripcion` TEXT, -- Proceso de Desarrollo de Aprendizaje
  `tipo` ENUM('contenido', 'eje_articulador') NOT NULL, -- Para diferenciar entre tipos de contenido
  `orden` INT(11) DEFAULT 0, -- Para ordenar la presentación en el formulario
  `activo` BOOLEAN NOT NULL DEFAULT TRUE,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_contenidos_curriculares_cursos`
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `formularios_dinamicos`
-- Almacena la definición del formulario en JSON.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `formularios_dinamicos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_curso` INT(11) NOT NULL UNIQUE, -- Un formulario dinámico por curso
  `nombre` VARCHAR(255) NOT NULL,
  `schema_json` JSON NOT NULL, -- Almacena la definición del formulario en formato JSON
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_formularios_dinamicos_cursos`
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `openai_assistant_configs`
-- Configuración de asistentes de OpenAI por curso.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `openai_assistant_configs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_curso` INT(11) NOT NULL UNIQUE, -- Un asistente por curso
  `assistant_id` VARCHAR(255) NOT NULL, -- ID del asistente de OpenAI
  `vector_store_id` VARCHAR(255) NULL, -- ID del Vector Store de OpenAI para RAG (opcional)
  `instrucciones_adicionales` TEXT, -- Prompt de sistema adicional específico del curso
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_openai_assistant_configs_cursos`
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `grupos`
-- Grupos creados por docentes, asociados a un curso.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `grupos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_docente` INT(11) NOT NULL, -- El docente propietario del grupo
  `id_curso` INT(11) NOT NULL, -- El curso al que pertenece este grupo
  `nombre_grupo` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_grupo_docente_curso_nombre` (`id_docente`, `id_curso`, `nombre_grupo`), -- Un docente no puede tener dos grupos con el mismo nombre para el mismo curso
  CONSTRAINT `fk_grupos_docente`
    FOREIGN KEY (`id_docente`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_grupos_curso`
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `estudiante_grupo`
-- Relación muchos a muchos entre Estudiantes y Grupos, con observaciones de inclusión.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `estudiante_grupo` (
  `id_estudiante` INT(11) NOT NULL,
  `id_grupo` INT(11) NOT NULL,
  `observaciones_inclusion` TEXT NULL, -- Notas específicas de inclusión para este estudiante en este grupo
  `fecha_asignacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_estudiante`, `id_grupo`), -- Clave primaria compuesta
  CONSTRAINT `fk_estudiante_grupo_estudiante`
    FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_estudiante_grupo_grupo`
    FOREIGN KEY (`id_grupo`) REFERENCES `grupos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Tabla `planeaciones`
-- Modificada para vincularse a `cursos` y `grupos`.
-- -----------------------------------------------------
-- Eliminar claves foráneas y columnas si existen para recrearlas con la estructura actualizada
DELIMITER $$
CREATE PROCEDURE UpdatePlaneacionesTable_planeacion_db2()
BEGIN
    IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones') THEN
        -- Eliminar FK si existe
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND constraint_name = 'fk_planeaciones_cursos') THEN
            ALTER TABLE `planeaciones` DROP FOREIGN KEY `fk_planeaciones_cursos`;
        END IF;
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND constraint_name = 'fk_planeaciones_cursos_new') THEN
            ALTER TABLE `planeaciones` DROP FOREIGN KEY `fk_planeaciones_cursos_new`;
        END IF;
        IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND constraint_name = 'fk_planeaciones_grupos') THEN
            ALTER TABLE `planeaciones` DROP FOREIGN KEY `fk_planeaciones_grupos`;
        END IF;

        -- Eliminar columnas si existen
        IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND column_name = 'id_curso') THEN
            ALTER TABLE `planeaciones` DROP COLUMN `id_curso`;
        END IF;

        IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND column_name = 'id_grupo') THEN
            ALTER TABLE `planeaciones` DROP COLUMN `id_grupo`;
        END IF;
    END IF;
END$$
DELIMITER ;

CALL UpdatePlaneacionesTable_planeacion_db2();
DROP PROCEDURE IF EXISTS UpdatePlaneacionesTable_planeacion_db2;

-- Creación de la tabla `planeaciones` si no existe, o añadir columnas si ya existe.
CREATE TABLE IF NOT EXISTS `planeaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `id_curso` INT(11) NULL, -- Nueva columna para vincular a la tabla `cursos`
  `id_grupo` INT(11) NULL, -- Nueva columna para vincular a la tabla `grupos` (opcional)
  `prompt_data` JSON NOT NULL,
  `respuesta_ia` LONGTEXT DEFAULT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `fk_planeaciones_usuarios`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_planeaciones_cursos_new`
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_planeaciones_grupos`
    FOREIGN KEY (`id_grupo`) REFERENCES `grupos`(`id`) ON DELETE SET NULL ON UPDATE CASCADE -- SET NULL si el grupo se elimina
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Añadir las columnas y FKs si la tabla ya existía pero no tenía estas columnas (después de la creación inicial)
DELIMITER $$
CREATE PROCEDURE AddPlaneacionesColumnsAndFKs_planeacion_db2()
BEGIN
    IF EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones') THEN
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND column_name = 'id_curso') THEN
            ALTER TABLE `planeaciones` ADD COLUMN `id_curso` INT(11) NULL AFTER `id_usuario`;
            ALTER TABLE `planeaciones` ADD CONSTRAINT `fk_planeaciones_cursos_new` FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        END IF;

        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'planeacion_db2' AND table_name = 'planeaciones' AND column_name = 'id_grupo') THEN
            ALTER TABLE `planeaciones` ADD COLUMN `id_grupo` INT(11) NULL AFTER `id_curso`;
            ALTER TABLE `planeaciones` ADD CONSTRAINT `fk_planeaciones_grupos` FOREIGN KEY (`id_grupo`) REFERENCES `grupos`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        END IF;
    END IF;
END$$
DELIMITER ;

CALL AddPlaneacionesColumnsAndFKs_planeacion_db2();
DROP PROCEDURE IF EXISTS AddPlaneacionesColumnsAndFKs_planeacion_db2;

