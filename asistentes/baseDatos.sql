--
-- Base de datos: `eventos`
--
CREATE DATABASE IF NOT EXISTS `eventos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci;

---
--- tabla asistentes
---
CREATE TABLE `asistentes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'LLave primaria de la tabla',
  `nombres` varchar(100) COLLATE utf8mb4_spanish2_ci NOT NULL COMMENT 'Nombre del asistente',
  `apellidos` varchar(100) COLLATE utf8mb4_spanish2_ci NOT NULL COMMENT 'Apellidos del asistente',
  `numerodocumento` varchar(30) COLLATE utf8mb4_spanish2_ci NOT NULL COMMENT 'Se registrar el número de documento',
  `tipodocumento` varchar(30) COLLATE utf8mb4_spanish2_ci NOT NULL COMMENT 'Tipo de documento',
  `telefonomovil` varchar(10) COLLATE utf8mb4_spanish2_ci NOT NULL COMMENT 'Registro del teléfono móvil',
  `correo` varchar(100) COLLATE utf8mb4_spanish2_ci NOT NULL COMMENT 'Correo del asistente',
  `estado` char(1) COLLATE utf8mb4_spanish2_ci DEFAULT NULL COMMENT 'Estado de asistencia',
  PRIMARY KEY (`id`),
  KEY `asistentes_id_IDX` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci