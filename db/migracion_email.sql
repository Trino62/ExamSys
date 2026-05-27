-- Agrega columna email del candidato a resultados (idempotente)
ALTER TABLE resultados
  ADD COLUMN IF NOT EXISTS email VARCHAR(255) DEFAULT NULL AFTER usuario;
