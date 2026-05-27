-- Migración: agrega columna template_slug a la tabla resultados
-- Solo necesaria si ya tenías la tabla sin esta columna.
-- En instalaciones nuevas preguntas.sql ya la incluye.

ALTER TABLE resultados
  ADD COLUMN IF NOT EXISTS template_slug CHAR(8) DEFAULT NULL;
