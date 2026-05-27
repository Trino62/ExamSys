-- Migración: tabla site_config para personalización del sitio
-- Ejecutar UNA sola vez:
--   mysql -u root -p examen < db/migracion_config.sql

CREATE TABLE IF NOT EXISTS site_config (
  clave VARCHAR(100) PRIMARY KEY,
  valor TEXT NOT NULL
) CHARACTER SET utf8mb4;

INSERT INTO site_config (clave, valor) VALUES
  ('titulo',        'Evaluación de Conocimientos en Programación Web'),
  ('descripcion',   'Una herramienta diseñada para conocer el nivel técnico de los candidatos que desean incorporarse a nuestro equipo.'),
  ('badges',        'HTML & CSS,JavaScript,PHP,MySQL'),
  ('texto_cta',     'El examen toma menos de 10 minutos. Responde con honestidad — los resultados sirven para apoyarte mejor.'),
  ('card1_icono',   '🎯'),
  ('card1_titulo',  '¿Para qué sirve?'),
  ('card1_desc',    'Permite identificar el nivel real de conocimiento de cada candidato antes de incorporarse al equipo, sin depender únicamente de su currículum.'),
  ('card2_icono',   '🏢'),
  ('card2_titulo',  '¿Cómo ayuda a la empresa?'),
  ('card2_desc',    'Reduce el tiempo de integración al detectar áreas de oportunidad desde el primer día, permitiendo asignar tareas y mentores adecuados a cada perfil.'),
  ('card3_icono',   '📋'),
  ('card3_titulo',  '¿Cómo funciona?'),
  ('card3_desc',    'Preguntas de opción múltiple sobre los temas evaluados. Al finalizar obtienes tu puntuación y un nivel que describe tu preparación.')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);
