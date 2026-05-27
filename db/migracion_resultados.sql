-- Migración: agrega columnas de métricas a la tabla resultados
-- Solo necesaria si ya tenías la tabla sin estas columnas.
-- En instalaciones nuevas preguntas.sql ya las incluye.

ALTER TABLE resultados
  ADD COLUMN IF NOT EXISTS tiempo_segundos INT     DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS cambios_foco    SMALLINT DEFAULT NULL;
