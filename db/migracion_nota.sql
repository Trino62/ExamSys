-- Agrega columna nota del reclutador a resultados (idempotente)
ALTER TABLE resultados
  ADD COLUMN IF NOT EXISTS nota TEXT DEFAULT NULL;
