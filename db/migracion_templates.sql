-- Migración: tabla exam_templates para exámenes configurables
-- Ejecutar UNA sola vez:
--   mysql -u root -p examen < db/migracion_templates.sql

CREATE TABLE IF NOT EXISTS exam_templates (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nombre          VARCHAR(255)  NOT NULL,
  slug            CHAR(8)       NOT NULL UNIQUE,
  tiempo_segundos INT           DEFAULT NULL,        -- NULL = sin límite
  config          JSON          NOT NULL,             -- {"HTML":2,"CSS":2,...}
  activa          TINYINT(1)    NOT NULL DEFAULT 1,
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4;

-- Plantilla por defecto (equivalente al comportamiento actual)
INSERT INTO exam_templates (nombre, slug, tiempo_segundos, config) VALUES (
  'Examen general',
  'default1',
  NULL,
  '{"HTML":2,"CSS":2,"JavaScript":2,"PHP":2,"MySQL":1,"Web":1}'
);
