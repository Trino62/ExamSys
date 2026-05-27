-- ============================================================
-- ExamSys — SQL de inicialización para Docker
-- Se ejecuta automáticamente al crear el volumen por primera vez
-- ============================================================

USE examen;

-- ── 1. Tablas base ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS preguntas (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  pregunta  TEXT         NOT NULL,
  opcion_a  VARCHAR(500) NOT NULL,
  opcion_b  VARCHAR(500) NOT NULL,
  opcion_c  VARCHAR(500) NOT NULL,
  opcion_d  VARCHAR(500) NOT NULL,
  correcta  TINYINT      NOT NULL COMMENT '0=A  1=B  2=C  3=D',
  categoria VARCHAR(50)  NOT NULL DEFAULT 'general'
) CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS resultados (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  usuario         VARCHAR(255) NOT NULL,
  email           VARCHAR(255) DEFAULT NULL,
  respuestas      LONGTEXT     NOT NULL,
  fecha           DATETIME     NOT NULL,
  tiempo_segundos INT          DEFAULT NULL,
  cambios_foco    SMALLINT     DEFAULT NULL,
  template_slug   CHAR(8)      DEFAULT NULL,
  nota            TEXT         DEFAULT NULL
) CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS exam_templates (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nombre          VARCHAR(255) NOT NULL,
  slug            CHAR(8)      NOT NULL UNIQUE,
  tiempo_segundos INT          DEFAULT NULL,
  config          JSON         NOT NULL,
  activa          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS site_config (
  clave VARCHAR(100) PRIMARY KEY,
  valor TEXT NOT NULL
) CHARACTER SET utf8mb4;

-- ── 2. Preguntas de ejemplo ───────────────────────────────────────────────────

INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria) VALUES
('¿Qué etiqueta se usa para el título principal de una página?',    '<title>',   '<h1>',    '<header>',  '<main>',    1, 'HTML'),
('¿Qué atributo de <a> define la URL del enlace?',                  'src',       'url',      'href',      'link',      2, 'HTML'),
('¿Qué etiqueta se usa para insertar una imagen?',                  '<image>',   '<picture>','<photo>',   '<img>',     3, 'HTML'),
('¿Qué etiqueta agrupa el contenido visible de la página?',         '<head>',    '<body>',   '<html>',    '<section>', 1, 'HTML'),
('¿Qué etiqueta crea un campo de texto en un formulario?',          '<textbox>', '<field>',  '<input>',   '<text>',    2, 'HTML');

INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria) VALUES
('¿Cómo se selecciona un elemento con clase "menu" en CSS?',        '#menu',          '.menu',          'menu',           '*menu',           1, 'CSS'),
('¿Qué propiedad CSS cambia el color del texto?',                   'font-color',     'text-color',     'color',          'foreground',      2, 'CSS'),
('¿Cómo se aplica CSS directamente en una etiqueta HTML?',          'Atributo class', 'Atributo css',   'Atributo style', 'Atributo design', 2, 'CSS'),
('¿Qué propiedad CSS controla el tamaño del texto?',                'text-size',      'font-size',      'size',           'font-weight',     1, 'CSS');

INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria) VALUES
('¿Cómo se imprime un mensaje en la consola del navegador?',                                'print()',                  'echo()',                     'console.log()',               'log()',                  2, 'JavaScript'),
('¿Cuál es la forma correcta de declarar una variable que puede cambiar?',                  'const',                    'let',                        'var',                         'def',                    1, 'JavaScript'),
('¿Cómo se selecciona un elemento HTML por su id desde JavaScript?',                        'document.find()',          'document.getElementById()',  'document.getElement()',       'document.query()',       1, 'JavaScript'),
('¿Qué hace array.push(valor)?',                                                            'Elimina el primer elemento','Obtiene la longitud',        'Agrega un elemento al final', 'Ordena el arreglo',      2, 'JavaScript'),
('¿Cómo se define una función en JavaScript?',                                              'def miFuncion() {}',       'func miFuncion() {}',        'function miFuncion() {}',     'method miFuncion() {}',  2, 'JavaScript'),
('¿Qué método convierte un objeto JavaScript a cadena JSON?',                               'JSON.parse()',             'JSON.decode()',              'JSON.convert()',              'JSON.stringify()',       3, 'JavaScript');

INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria) VALUES
('¿Con qué etiqueta se abre un bloque de código PHP?',                           '<php>',        '<?php',         '<%php',              '{php}',               1, 'PHP'),
('¿Cómo se declara una variable en PHP?',                                        'var nombre',   'let nombre',    '$nombre',            '#nombre',             2, 'PHP'),
('¿Cómo se muestra texto en PHP?',                                               'print_r()',    'console.log()', 'write',              'echo',                3, 'PHP'),
('¿Qué instrucción incluye un archivo PHP y detiene si no existe?',              'import',       'include',       'require',            'use',                 2, 'PHP'),
('¿Cómo se concatena texto en PHP?',                                             'Con +',        'Con &',         'Con .',              'Con ||',              2, 'PHP'),
('¿Qué función convierte un JSON a arreglo en PHP?',                             'json_parse()', 'json_decode()', 'json_convert()',     'json_array()',        1, 'PHP');

INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria) VALUES
('¿Qué instrucción SQL se usa para obtener datos de una tabla?',                 'GET',    'FETCH',  'SELECT',                                             'READ',                                         2, 'MySQL'),
('¿Qué instrucción SQL agrega un nuevo registro?',                               'ADD INTO','INSERT INTO','PUT INTO',                                      'APPEND',                                       1, 'MySQL'),
('¿Qué cláusula SQL filtra los resultados?',                                     'FILTER', 'HAVING', 'WHERE',                                              'LIMIT',                                        2, 'MySQL'),
('¿Qué es la llave primaria (PRIMARY KEY)?',                                     'La primera columna de la tabla','Un campo que identifica de forma única cada fila','Una contraseña de acceso','Un índice con duplicados', 1, 'MySQL'),
('¿Qué instrucción SQL actualiza datos existentes?',                             'MODIFY', 'CHANGE', 'SET',                                                'UPDATE',                                       3, 'MySQL');

INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta, categoria) VALUES
('¿Qué significa "frontend"?',                          'La base de datos',      'El servidor',    'La parte visual en el navegador', 'El sistema operativo', 2, 'Web'),
('¿Qué es el backend?',                                 'El código en el servidor','Los estilos CSS','El navegador web',               'La interfaz gráfica',  0, 'Web'),
('¿Qué método HTTP se usa para enviar datos de un formulario?', 'GET','POST','PUT','DELETE',                                                                1, 'Web'),
('¿Qué es una API?',                                    'Un tipo de base de datos','Un lenguaje',   'Una interfaz entre aplicaciones', 'Un framework de CSS',  2, 'Web');

-- ── 3. Plantilla de examen por defecto ───────────────────────────────────────

INSERT INTO exam_templates (nombre, slug, tiempo_segundos, config) VALUES (
  'Examen general',
  'default1',
  NULL,
  '{"HTML":2,"CSS":2,"JavaScript":2,"PHP":2,"MySQL":1,"Web":1}'
);

-- ── 4. Configuración del sitio por defecto ────────────────────────────────────

INSERT INTO site_config (clave, valor) VALUES
  ('titulo',       'Evaluación de Conocimientos'),
  ('descripcion',  'Una herramienta para conocer el nivel técnico de los candidatos.'),
  ('badges',       'HTML & CSS,JavaScript,PHP,MySQL'),
  ('texto_cta',    'El examen toma menos de 10 minutos. Responde con honestidad.'),
  ('card1_icono',  '🎯'),
  ('card1_titulo', '¿Para qué sirve?'),
  ('card1_desc',   'Identifica el nivel real de conocimiento antes de incorporar a alguien al equipo.'),
  ('card2_icono',  '🏢'),
  ('card2_titulo', '¿Cómo ayuda?'),
  ('card2_desc',   'Detecta áreas de oportunidad desde el primer día para asignar tareas y mentores adecuados.'),
  ('card3_icono',  '📋'),
  ('card3_titulo', '¿Cómo funciona?'),
  ('card3_desc',   'Preguntas de opción múltiple. Al finalizar obtienes tu puntuación y un nivel de preparación.')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);
