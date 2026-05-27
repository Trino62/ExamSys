-- Preguntas adicionales para el banco de examen
-- Importar con: mysql -u root -p examen < db/preguntas_extra.sql
-- Lleva el banco de 32 a 71 preguntas (10 al azar por examen)

-- ─── HTML ────────────────────────────────────────────────────────────────────
INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta) VALUES
('¿Qué etiqueta crea una lista ordenada (numerada)?',
 '<ul>', '<ol>', '<list>', '<dl>', 1),

('¿Qué etiqueta define un elemento dentro de una lista?',
 '<item>', '<li>', '<ul>', '<dt>', 1),

('¿Qué atributo de <img> muestra un texto alternativo si la imagen no carga?',
 'title', 'alt', 'desc', 'caption', 1),

('¿Qué etiqueta se usa para resaltar texto en negrita con significado semántico?',
 '<bold>', '<b>', '<strong>', '<em>', 2),

('¿Cuál es la sintaxis correcta para un comentario en HTML?',
 '// comentario', '/* comentario */', '<!-- comentario -->', '# comentario', 2),

('¿Qué etiqueta se usa para crear un salto de línea?',
 '<break>', '<lb>', '<br>', '<nl>', 2),

('¿Qué etiqueta define el área de metadatos de un documento HTML (no visible)?',
 '<body>', '<meta>', '<head>', '<info>', 2),

('¿Qué atributo de un <input type="submit"> define el texto del botón?',
 'label', 'name', 'value', 'text', 2);

-- ─── CSS ─────────────────────────────────────────────────────────────────────
INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta) VALUES
('¿Qué propiedad CSS agrega espacio interior entre el contenido y el borde del elemento?',
 'margin', 'padding', 'border', 'spacing', 1),

('¿Qué propiedad CSS agrega espacio exterior entre el elemento y los demás?',
 'padding', 'margin', 'border', 'gap', 1),

('¿Qué hace `display: flex` en CSS?',
 'Oculta el elemento', 'Convierte el elemento en inline', 'Activa el modelo de caja flexible (flexbox)', 'Centra el texto automáticamente', 2),

('¿Qué propiedad CSS controla la transparencia de un elemento?',
 'visibility', 'transparency', 'opacity', 'alpha', 2),

('¿Cómo se escribe un comentario en CSS?',
 '// comentario', '<!-- comentario -->', '/* comentario */', '# comentario', 2),

('¿Qué propiedad CSS cambia el color de fondo de un elemento?',
 'color', 'background', 'background-color', 'fill', 2),

('¿Qué valor de `position` fija el elemento en pantalla al hacer scroll?',
 'absolute', 'relative', 'fixed', 'sticky', 2),

('¿Qué hace `border-radius` en CSS?',
 'Agrega una sombra al elemento', 'Redondea las esquinas del elemento', 'Define el grosor del borde', 'Cambia el color del borde', 1);

-- ─── JAVASCRIPT ──────────────────────────────────────────────────────────────
INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta) VALUES
('¿Qué operador compara valor Y tipo en JavaScript?',
 '==', '===', '=', '!=', 1),

('¿Qué hace `array.length`?',
 'Agrega un elemento al arreglo', 'Elimina el último elemento', 'Devuelve el número de elementos', 'Ordena el arreglo', 2),

('¿Qué hace `array.forEach(callback)`?',
 'Filtra elementos del arreglo', 'Ejecuta una función por cada elemento', 'Devuelve un nuevo arreglo transformado', 'Busca un elemento por valor', 1),

('¿Qué hace `document.querySelector(".btn")`?',
 'Selecciona todos los elementos con clase btn', 'Selecciona el primer elemento con clase btn', 'Elimina el elemento del DOM', 'Crea un elemento con clase btn', 1),

('¿Qué hace `parseInt("42abc")`?',
 'Lanza un error', 'Devuelve NaN', 'Devuelve 42', 'Devuelve "42abc"', 2),

('¿Qué es un objeto en JavaScript?',
 'Un tipo especial de función', 'Una colección de pares clave-valor', 'Una variable de solo lectura', 'Un método del DOM', 1),

('¿Qué hace `array.filter(fn)`?',
 'Ordena el arreglo según la función', 'Modifica cada elemento del arreglo', 'Devuelve un nuevo arreglo con los elementos que cumplen la condición', 'Agrupa elementos por categoría', 2),

('¿Qué hace `array.map(fn)`?',
 'Filtra los elementos que cumplan la condición', 'Devuelve un nuevo arreglo con cada elemento transformado', 'Agrega elementos al final', 'Busca un elemento por índice', 1),

('¿Cómo se accede al valor de un input desde JavaScript?',
 'document.getElementById("id").text', 'document.getElementById("id").value', 'document.getElementById("id").content', 'document.getElementById("id").data', 1);

-- ─── PHP ─────────────────────────────────────────────────────────────────────
INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta) VALUES
('¿Qué hace `isset($var)` en PHP?',
 'Asigna un valor a la variable', 'Verifica si la variable existe y no es null', 'Elimina la variable', 'Imprime el valor de la variable', 1),

('¿Cuál es la forma correcta de crear un arreglo en PHP moderno?',
 '$arr = {}', '$arr = []', '$arr = ()', '$arr = new Array()', 1),

('¿Qué hace `count($arr)` en PHP?',
 'Suma los valores numéricos del arreglo', 'Devuelve el número de elementos', 'Ordena el arreglo', 'Verifica si el arreglo está vacío', 1),

('¿Qué función agrega un elemento al final de un arreglo en PHP?',
 'array_unshift()', 'array_add()', 'array_push()', 'array_append()', 2),

('¿Qué función convierte un arreglo PHP a cadena JSON para enviar al frontend?',
 'json_stringify()', 'json_encode()', 'array_to_json()', 'to_json()', 1),

('¿Qué superglobal contiene los parámetros enviados por URL (query string)?',
 '$_POST', '$_GET', '$_URL', '$_QUERY', 1),

('¿Qué función verifica si un valor existe dentro de un arreglo PHP?',
 'array_exists()', 'isset()', 'in_array()', 'array_contains()', 2),

('¿Cómo se accede al segundo elemento de `$arr` en PHP?',
 '$arr[2]', '$arr[1]', '$arr->1', '$arr.1', 1),

('¿Qué hace `str_replace("a", "e", $texto)` en PHP?',
 'Busca "a" en $texto y devuelve su posición', 'Reemplaza todas las ocurrencias de "a" por "e" en $texto', 'Divide $texto cada vez que aparece "a"', 'Elimina todas las "a" de $texto', 1);

-- ─── MYSQL ───────────────────────────────────────────────────────────────────
INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta) VALUES
('¿Qué cláusula SQL ordena los resultados de una consulta?',
 'SORT BY', 'ORDER BY', 'GROUP BY', 'HAVING', 1),

('¿Qué cláusula SQL limita el número de filas devueltas?',
 'TOP', 'MAX', 'LIMIT', 'FETCH', 2),

('¿Qué instrucción elimina una tabla completa de la base de datos?',
 'DELETE TABLE', 'REMOVE TABLE', 'TRUNCATE TABLE', 'DROP TABLE', 3),

('¿Qué hace `COUNT(*)` en una consulta SQL?',
 'Suma los valores de una columna numérica', 'Cuenta el total de filas del resultado', 'Devuelve el valor máximo de la columna', 'Cuenta solo las filas con valores únicos', 1),

('¿Qué tipo de dato MySQL se usa para almacenar texto de longitud variable (hasta 255 chars)?',
 'CHAR', 'VARCHAR', 'TEXT', 'STRING', 1),

('¿Qué hace un JOIN en SQL?',
 'Divide una tabla en dos', 'Combina filas de dos o más tablas según una condición', 'Crea un índice en la tabla', 'Duplica los registros de una tabla', 1),

('¿Qué hace `AUTO_INCREMENT` en MySQL?',
 'Genera un valor único y creciente automáticamente para cada nuevo registro', 'Incrementa todos los valores de la columna en 1', 'Ordena la tabla automáticamente', 'Crea una copia de seguridad', 0),

('¿Qué cláusula SQL agrupa filas con valores iguales en una columna?',
 'ORDER BY', 'HAVING', 'GROUP BY', 'DISTINCT', 2);

-- ─── CONCEPTOS WEB ───────────────────────────────────────────────────────────
INSERT INTO preguntas (pregunta, opcion_a, opcion_b, opcion_c, opcion_d, correcta) VALUES
('¿Qué significa HTTPS?',
 'HTTP con mayor velocidad de transferencia', 'HTTP con cifrado SSL/TLS para comunicaciones seguras', 'HTTP para servidores de alto rendimiento', 'HTTP con soporte para imágenes', 1),

('¿Qué es el DOM?',
 'Un tipo de base de datos relacional', 'Un lenguaje de estilos para HTML', 'La representación en árbol de los elementos HTML de una página', 'Un protocolo de red', 2),

('¿Para qué sirve Git?',
 'Diseñar interfaces de usuario', 'Controlar versiones del código fuente', 'Conectarse a bases de datos', 'Compilar código PHP', 1),

('¿Qué es la responsividad en diseño web?',
 'La velocidad de carga de la página', 'La capacidad de adaptar el diseño a distintos tamaños de pantalla', 'La seguridad del sitio ante ataques', 'La capacidad de guardar datos sin conexión', 1),

('¿Qué es una sesión en desarrollo web?',
 'Una conexión permanente entre cliente y servidor', 'Un mecanismo para mantener datos del usuario entre peticiones HTTP', 'Un tipo de base de datos temporal', 'Un archivo de configuración del servidor', 1),

('¿Qué herramienta del navegador permite ver errores de JavaScript y peticiones de red?',
 'El editor de código', 'Las herramientas de desarrollador (DevTools)', 'El administrador de tareas', 'El historial del navegador', 1),

('¿Qué significa "full stack"?',
 'Un desarrollador que solo trabaja con bases de datos', 'Un desarrollador que trabaja tanto en frontend como en backend', 'Un framework de JavaScript', 'Una arquitectura de microservicios', 1);
