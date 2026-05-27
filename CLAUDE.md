# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de evaluación configurable. Los candidatos responden un examen de opción múltiple (preguntas balanceadas por categoría), los resultados se guardan en MySQL y el admin los consulta con búsqueda, filtros, estadísticas y PDF. El sistema es genérico: el nombre, descripción, badges y tarjetas de la landing se configuran desde el panel de administración sin tocar código.

## Stack

- Frontend: HTML5 + JavaScript vanilla (fetch API); Bootstrap 5 solo en `index.php`
- Backend: PHP con PDO
- Base de datos: MySQL en `localhost:3307`, base `examen`

## Ejecución

No hay proceso de build. Requiere servidor web con PHP y MySQL:

```bash
php -S localhost:8080
```

## Instalación

La forma recomendada es usar el wizard en `install.php` (ver README). Para instalación manual por CLI:

```bash
# 1. Tablas base + seed de preguntas (incluye categorías y resultados)
mysql -u root -p examen < db/preguntas.sql

# 2. Columnas de métricas en resultados (idempotente, seguro correr siempre)
mysql -u root -p examen < db/migracion_resultados.sql

# 3. Columna template_slug en resultados
mysql -u root -p examen < db/migracion_template_slug.sql

# 4. Columna email del candidato en resultados
mysql -u root -p examen < db/migracion_email.sql

# 5. Columna nota del reclutador en resultados
mysql -u root -p examen < db/migracion_nota.sql

# 6. Tabla de plantillas de examen
mysql -u root -p examen < db/migracion_templates.sql

# 7. Tabla de configuración del sitio
mysql -u root -p examen < db/migracion_config.sql

# 8. Preguntas extra (opcional, banco más amplio)
mysql -u root -p examen < db/preguntas_extra.sql
```

## Schema completo

```sql
CREATE DATABASE examen CHARACTER SET utf8mb4;
USE examen;

CREATE TABLE preguntas (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  pregunta  TEXT         NOT NULL,
  opcion_a  VARCHAR(500) NOT NULL,
  opcion_b  VARCHAR(500) NOT NULL,
  opcion_c  VARCHAR(500) NOT NULL,
  opcion_d  VARCHAR(500) NOT NULL,
  correcta  TINYINT      NOT NULL,   -- 0=A  1=B  2=C  3=D
  categoria VARCHAR(50)  NOT NULL DEFAULT 'general'
) CHARACTER SET utf8mb4;

CREATE TABLE resultados (
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

CREATE TABLE exam_templates (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nombre          VARCHAR(255) NOT NULL,
  slug            CHAR(8)      NOT NULL UNIQUE,
  tiempo_segundos INT          DEFAULT NULL,   -- NULL = sin límite
  config          JSON         NOT NULL,        -- {"HTML":2,"CSS":2,...}
  activa          TINYINT(1)   NOT NULL DEFAULT 1,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4;

CREATE TABLE site_config (
  clave VARCHAR(100) PRIMARY KEY,
  valor TEXT NOT NULL
) CHARACTER SET utf8mb4;
```

## Arquitectura y flujo de datos

```
index.php ──────────────────────────────────────────────────────────────────┐
  │  (PIN modal → server/auth.php → $_SESSION['admin'])                     │
  └→ examen.html?t=SLUG                                                      │
       │  GET server/preguntas.php?t=SLUG  →  MySQL.preguntas (UNION/cat)   │
       │  POST server/resultados.php       →  MySQL.resultados              │
       └→ index.php (btn terminar)                                           │
                                                                             │
  (admin autenticado) ──────────────────────────────────────────────────────┘
  exames.php  →  detalle_examen.php?id=X  →  server/resultado_unico.php
  estadisticas.php
  admin.php
    ├── Plantillas  →  server/templates.php
    ├── Preguntas   →  server/preguntas_crud.php
    └── Sitio       →  server/config.php
```

**Plantillas de examen:** Cada plantilla tiene un `slug` de 8 chars. El link `examen.html?t=SLUG` carga esa configuración (categorías, n° preguntas, tiempo límite). Sin `?t=` usa config por defecto balanceada.

**Banco de preguntas:** Selección balanceada por categoría usando UNION ALL. El config JSON de la plantilla determina cuántas preguntas por categoría: `{"HTML":2,"CSS":2,"JavaScript":2,"PHP":2,"MySQL":1,"Web":1}`.

**Almacenamiento de respuestas:** `userAnswers` serializado como JSON en `respuestas`. Índice `0` = nombre del alumno. Cada elemento: `{ q, res, resCorrecta, correct, options, categoria }`. La calificación y el desglose por categoría se reconstruyen en cliente sin consultar `preguntas`.

**Comunicación frontend↔backend:** Todo vía `fetch` + `Content-Type: application/json`. El backend lee con `file_get_contents('php://input')`.

**Autenticación admin:** PIN validado en `server/auth.php` → sesión PHP (`$_SESSION['admin'] = true`). Todas las páginas admin hacen `session_start()` + check al inicio. Logout en `server/logout.php`.

**Configuración del sitio:** `site_config` tabla clave-valor. `server/config.php` es GET público (lo lee `examen.html` para el título) y POST solo admin.

**Niveles de conocimiento** (% de aciertos, calculado en PHP y JS):

| Nivel | % aciertos | Color |
|---|---|---|
| Sin base | < 21 % | `#e53935` |
| Básico | 21 – 40 % | `#fb8c00` |
| En desarrollo | 41 – 61 % | `#039be5` |
| Aceptable | 62 – 81 % | `#5c6bc0` |
| Destacado | ≥ 82 % | `#43a047` |

## Archivos clave

### Páginas públicas
- [`index.php`](index.php) — Landing dinámica; lee `site_config` de la BD. `index.html` redirige aquí.
- [`examen.html`](examen.html) — Quiz: carga preguntas del server, countdown opcional, métricas de tiempo y foco, envía resultado.

### Páginas admin (requieren sesión)
- [`exames.php`](exames.php) — Lista de resultados; filtros por nombre, nivel, plantilla y fecha; borrar.
- [`detalle_examen.php`](detalle_examen.php) — Detalle: score card, desglose por categoría, notas del reclutador, PDF, borrar.
- [`estadisticas.php`](estadisticas.php) — Dashboard: distribución por nivel, rendimiento por categoría.
- [`admin.php`](admin.php) — Panel admin con 3 pestañas: Plantillas / Preguntas / Configuración del sitio.

### Server (API)
- [`server/conexion.php`](server/conexion.php) — PDO wrapper. Lee credenciales de variables de entorno (`.env`).
- [`server/auth.php`](server/auth.php) — Valida PIN con anti-brute-force (5 intentos → lockout 5 min). PIN desde `ADMIN_PIN` en `.env`.
- [`server/logout.php`](server/logout.php) — Destruye sesión, redirige a `index.php`.
- [`server/preguntas.php`](server/preguntas.php) — GET `?t=SLUG`: devuelve `{questions, tiempo_segundos}` según plantilla.
- [`server/resultados.php`](server/resultados.php) — POST: guarda resultado con tiempo, foco, email y template_slug. Zona horaria: `America/Mexico_City`.
- [`server/resultado_unico.php`](server/resultado_unico.php) — POST `{id}`: devuelve resultado completo incluyendo email, nota y template_nombre.
- [`server/eliminar_resultado.php`](server/eliminar_resultado.php) — POST `{id}`: borra resultado (solo admin).
- [`server/guardar_nota.php`](server/guardar_nota.php) — POST `{id, nota}`: guarda nota del reclutador en resultado (solo admin).
- [`server/templates.php`](server/templates.php) — CRUD de plantillas de examen.
- [`server/preguntas_crud.php`](server/preguntas_crud.php) — CRUD del banco de preguntas.
- [`server/importar_preguntas.php`](server/importar_preguntas.php) — GET `?template=1`: descarga plantilla CSV. POST: importa preguntas desde CSV.
- [`server/exportar_resultados.php`](server/exportar_resultados.php) — GET (admin): descarga todos los resultados como CSV (incluye email, nota y plantilla).
- [`server/config.php`](server/config.php) — GET (público) / POST (admin): configuración del sitio.
- [`server/site_config_helper.php`](server/site_config_helper.php) — `getSiteConfig(PDO)`: helper PHP para incluir en páginas.

### Base de datos / migraciones
- [`db/preguntas.sql`](db/preguntas.sql) — DDL completo: tablas `preguntas` y `resultados` (schema actual), más seed inicial.
- [`db/migracion_resultados.sql`](db/migracion_resultados.sql) — Agrega `tiempo_segundos` y `cambios_foco` a `resultados`.
- [`db/migracion_template_slug.sql`](db/migracion_template_slug.sql) — Agrega `template_slug` a `resultados`.
- [`db/migracion_email.sql`](db/migracion_email.sql) — Agrega `email` del candidato a `resultados`.
- [`db/migracion_nota.sql`](db/migracion_nota.sql) — Agrega `nota` del reclutador a `resultados`.
- [`db/migracion_templates.sql`](db/migracion_templates.sql) — Crea tabla `exam_templates` con plantilla por defecto.
- [`db/migracion_config.sql`](db/migracion_config.sql) — Crea tabla `site_config` con valores por defecto.
- [`db/preguntas_extra.sql`](db/preguntas_extra.sql) — 39 preguntas adicionales (opcional).

### Seguridad
- [`.htaccess`](.htaccess) — Bloquea dotfiles, `*.sql`, `docker-compose.yml`, `Dockerfile`, `CLAUDE.md` y deshabilita listado de directorios.
- [`db/.htaccess`](db/.htaccess) — Bloquea acceso web directo al directorio `db/`.
- [`docker/.htaccess`](docker/.htaccess) — Bloquea acceso web directo al directorio `docker/`.

## Notas de desarrollo

- **Agregar categorías nuevas:** Desde `admin.php` → Banco de preguntas → Nueva pregunta → seleccionar "➕ Nueva categoría…". No requiere cambios en código ni migraciones.
- **Agregar preguntas:** Desde `admin.php` → Banco de preguntas, o con INSERT directo en MySQL.
- **Cambiar PIN o credenciales DB:** Editar el archivo `.env` (o las variables de entorno en Docker).
- **Email del candidato:** Campo opcional capturado en la pantalla inicial del examen (antes de la pregunta 1). Se valida formato en cliente y servidor. Se muestra en lista, detalle y CSV.
- **Notas del reclutador:** Textarea editable en `detalle_examen.php`. Auto-guardado: debounce 1.5 s mientras escribe + blur. Endpoint: `server/guardar_nota.php`. Las notas aparecen en el PDF (sin borde ni botón en `@media print`).
- **Anti-brute-force PIN:** 5 intentos fallidos → lockout 5 minutos. Estado en `$_SESSION`. El modal muestra countdown en tiempo real y se re-habilita solo al expirar. `usleep(400ms)` en cada fallo para ralentizar ataques.
- **Lock de instalación:** `install.php` escribe `.installed` tras éxito. Guard principal: si `.installed` existe → redirect inmediato sin verificar BD. `.installed` está en `.gitignore` y protegido por `.htaccess`.
- **PDF del examen:** `window.print()` con `@media print` que oculta navegación, cambios de foco y desglose de categorías. El título del PDF usa `document.title = 'examen-{slug-nombre}'`.
- **Countdown:** Si la plantilla tiene `tiempo_segundos`, aparece en el header del examen. A ≤30s pulsa en rojo. Al llegar a 0 auto-envía con las respuestas actuales.
- **View Transitions:** `@view-transition { navigation: auto }` en todas las páginas para transiciones cross-document. Transiciones same-document en `examen.html` con `document.startViewTransition()`.
- **Modo oscuro:** Todas las páginas soportan dark mode. Clave `examsys_theme` en `localStorage` (`'dark'`/`'light'`). Script de prevención de FOUC en `<head>` de cada página; si no hay preferencia guardada, lee `prefers-color-scheme`. Botón 🌙/☀️ fijo en esquina inferior derecha (`position:fixed; bottom:22px; right:20px`). Implementado con CSS custom properties (`:root` / `[data-theme="dark"]`) más overrides por página.
- **Responsive `examen.html`:** `@media (max-width:480px)` reduce padding de card y opciones, achica el score-num y ajusta márgenes de review/botón. `@media (max-width:360px)` para pantallas muy pequeñas.
- **Responsive `admin.php`:** Tres breakpoints: `≤640px` — header compacto, tabs con scroll horizontal oculto, padding reducido; `≤480px` — plantillas en 1 columna, question items con badge+botones en primera línea y texto completo en segunda, modales en columna; `≤360px` — ajustes ultra-pequeños. La clase `.panel` (usada en el tab Sitio) está definida en CSS con `var(--bg-card)` y `var(--sh)` para compatibilidad con dark mode.
