# ExamSys

> Sistema de evaluación configurable con opción múltiple — diseñado para evaluar candidatos en cualquier área de conocimiento.

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## ✨ Características

- 🔗 **Links únicos por examen** — comparte un enlace con cada candidato; sin acceso libre
- 📋 **Plantillas configurables** — define categorías, número de preguntas y tiempo límite por plantilla
- 🏷️ **Categorías dinámicas** — agrega cualquier tema desde el panel sin tocar código ni migraciones
- 📊 **Panel de resultados** — busca y filtra por nombre, nivel, plantilla y fecha; desglose por categoría
- 📧 **Email del candidato** — capturado opcionalmente en la pantalla inicial del examen
- 📝 **Notas del reclutador** — campo editable por resultado con auto-guardado; aparece en el PDF
- 📈 **Estadísticas** — distribución de niveles y rendimiento por categoría
- ⏱️ **Countdown opcional** — cada plantilla puede tener su propio límite de tiempo
- ⚙️ **Sitio configurable** — cambia nombre, descripción y badges desde el panel sin editar código
- 🔒 **Anti-brute-force** — el PIN bloquea por 5 minutos tras 5 intentos fallidos
- 🌙 **Modo oscuro** — toggle manual + respeta `prefers-color-scheme`; preferencia guardada en `localStorage`
- 📱 **Responsive** — examen optimizado para móvil; panel admin funcional en tablet
- 🐳 **Docker-ready** — levanta todo con un comando
- 🧙 **Wizard de instalación** — configura la BD y el PIN desde el navegador

---

## 🚀 Inicio rápido con Docker

```bash
git clone https://github.com/Trino62/ExamSys.git
cd examsys
docker-compose up -d
```

Abre **http://localhost:8080** en tu navegador.

La base de datos se inicializa automáticamente con preguntas de ejemplo y una plantilla por defecto.

> **PIN de admin por defecto:** `1234`
> Cámbialo en `docker-compose.yml` → `environment.ADMIN_PIN` antes de usar en producción.

---

## 🛠️ Instalación manual

### Requisitos

- PHP 8.1+ con extensión `pdo_mysql`
- MySQL 8.0+ (o MariaDB 10.6+)
- Servidor web (Apache, Nginx, o `php -S`)

### Pasos

**1. Clona el repositorio**

```bash
git clone https://github.com/Trino62/ExamSys.git
cd examsys
```

**2. Abre el asistente de instalación**

```
http://tu-servidor/install.php
```

El wizard:
- Prueba la conexión a MySQL
- Crea la base de datos si no existe
- Ejecuta todas las migraciones y carga preguntas de ejemplo
- Genera el archivo `.env` con tus credenciales

**Listo.** Una vez instalado, `install.php` redirige automáticamente a la landing.

> ⚠️ **Recomendación de seguridad:** elimina o renombra `install.php` en el servidor una vez completada la instalación para evitar que pueda ser re-ejecutado.

### Instalación sin wizard (CLI)

```bash
# 1. Crear .env
cp .env.example .env
nano .env   # edita las variables

# 2. Crear base de datos
mysql -u root -p -e "CREATE DATABASE examen CHARACTER SET utf8mb4;"

# 3. Ejecutar migraciones en orden
mysql -u root -p examen < db/preguntas.sql
mysql -u root -p examen < db/migracion_resultados.sql
mysql -u root -p examen < db/migracion_template_slug.sql
mysql -u root -p examen < db/migracion_email.sql
mysql -u root -p examen < db/migracion_nota.sql
mysql -u root -p examen < db/migracion_templates.sql
mysql -u root -p examen < db/migracion_config.sql
mysql -u root -p examen < db/preguntas_extra.sql   # opcional

# 4. Levantar servidor de desarrollo
php -S localhost:8080
```

### 🔒 Seguridad en producción

El repositorio incluye un `.htaccess` que protege automáticamente los archivos sensibles en **Apache**. Verifica que tu VirtualHost tenga `AllowOverride All`:

```apache
<Directory /var/www/html/>
    AllowOverride All
    Options -Indexes
</Directory>
```

Si usas **Nginx**, agrega estas reglas en tu bloque `server {}`:

```nginx
# Bloquear dotfiles (.env, .gitignore, etc.)
location ~ /\. {
    deny all;
}

# Bloquear archivos SQL
location ~* \.sql$ {
    deny all;
}

# Bloquear directorios sensibles
location ~ ^/(db|docker)/ {
    deny all;
}

# Deshabilitar listado de directorios
autoindex off;
```

> `php -S` es solo para desarrollo local — no usar en producción.

---

## ⚙️ Variables de entorno

Configúralas en `.env` (instalación manual) o en `docker-compose.yml` (Docker):

| Variable     | Descripción                         | Por defecto   |
|--------------|-------------------------------------|---------------|
| `DB_HOST`    | Host de MySQL                       | `localhost`   |
| `DB_PORT`    | Puerto de MySQL                     | `3306`        |
| `DB_NAME`    | Nombre de la base de datos          | `examen`      |
| `DB_USER`    | Usuario de MySQL                    | `root`        |
| `DB_PASS`    | Contraseña de MySQL                 | *(vacío)*     |
| `ADMIN_PIN`  | PIN de 4 dígitos para el panel admin| `1234`        |

---

## 🎮 Uso

### Crear y compartir un examen

1. Entra al panel admin (botón **"Ver resultados"** en la landing → PIN)
2. Ve a **Plantillas** → **Nueva plantilla**
3. Configura nombre, categorías activas, preguntas por categoría y tiempo límite
4. Copia el **enlace** generado y envíaselo al candidato

### Gestionar preguntas

- **Panel admin → Banco de preguntas → Nueva pregunta**
- Para agregar una categoría nueva selecciona **"➕ Nueva categoría…"** en el campo categoría
- También puedes hacer INSERT directo en la tabla `preguntas`

### Personalizar la landing

**Panel admin → Configuración del sitio** — cambia título, descripción, badges y las tres tarjetas informativas.

---

## 📂 Estructura del proyecto

```
├── index.php                  # Landing pública (dinámica desde site_config)
├── index.html                 # Redirige a index.php
├── examen.html                # Página del examen (requiere ?t=SLUG)
├── exames.php                 # Lista de resultados (admin)
├── detalle_examen.php         # Detalle de un resultado con PDF (admin)
├── estadisticas.php           # Dashboard estadístico (admin)
├── admin.php                  # Panel admin: plantillas, preguntas, config
├── install.php                # Wizard de primera instalación
│
├── server/
│   ├── env.php                # Cargador de .env
│   ├── conexion.php           # Wrapper PDO (lee variables de entorno)
│   ├── auth.php               # Autenticación con PIN → sesión PHP
│   ├── logout.php             # Destruye sesión y redirige
│   ├── preguntas.php          # GET: preguntas balanceadas por plantilla
│   ├── resultados.php         # POST: guardar resultado
│   ├── resultado_unico.php    # POST: obtener un resultado por id
│   ├── eliminar_resultado.php # POST: borrar resultado (solo admin)
│   ├── templates.php          # CRUD de plantillas
│   ├── preguntas_crud.php     # CRUD del banco de preguntas
│   ├── importar_preguntas.php # POST: importar preguntas desde CSV
│   ├── exportar_resultados.php # GET: descarga todos los resultados como CSV
│   ├── config.php             # GET público / POST admin para site_config
│   └── site_config_helper.php # Helper PHP para leer site_config
│
├── db/
│   ├── preguntas.sql              # DDL completo + seed inicial
│   ├── migracion_resultados.sql   # Agrega tiempo_segundos y cambios_foco
│   ├── migracion_template_slug.sql # Agrega template_slug a resultados
│   ├── migracion_templates.sql    # Crea exam_templates
│   ├── migracion_config.sql       # Crea site_config
│   └── preguntas_extra.sql        # 39 preguntas adicionales (opcional)
│
├── docker/
│   └── init.sql               # SQL consolidado para Docker
│
├── Dockerfile
├── docker-compose.yml
├── .env.example
├── .dockerignore
├── .gitignore
└── README.md
```

---

## 🐳 Docker — comandos útiles

```bash
# Levantar (primera vez: construye imagen e inicializa BD)
docker-compose up -d

# Ver logs del contenedor PHP
docker-compose logs -f app

# Ver logs de MySQL
docker-compose logs -f db

# Reiniciar el app sin perder datos
docker-compose restart app

# Detener y eliminar contenedores (conserva datos en volumen)
docker-compose down

# Eliminar todo incluyendo datos de BD
docker-compose down -v
```

La app corre en **:8080**, MySQL queda expuesto en **:3307** para conectarse con clientes externos.

---

## 🧩 Adaptar a cualquier industria

ExamSys no está atado a programación. Para usarlo en otro dominio:

1. **Borra las preguntas de ejemplo** desde el panel admin → Banco de preguntas
2. **Agrega tus propias preguntas** con las categorías que necesites
3. **Actualiza la configuración del sitio** (título, descripción, badges) desde el panel
4. **Crea una plantilla** con las categorías y el número de preguntas adecuadas

No se requiere ningún cambio en el código.

---

## 📊 Niveles de conocimiento

| Nivel | % de aciertos | Color |
|---|---|---|
| Sin base | < 21 % | 🔴 Rojo |
| Básico | 21 – 40 % | 🟠 Naranja |
| En desarrollo | 41 – 61 % | 🔵 Azul |
| Aceptable | 62 – 81 % | 💜 Índigo |
| Destacado | ≥ 82 % | 🟢 Verde |

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas!

1. Haz fork del repositorio
2. Crea una rama (`git checkout -b feature/mi-feature`)
3. Haz commit de tus cambios
4. Abre un Pull Request con descripción clara de los cambios

Puedes revisar los issues abiertos para encontrar ideas donde contribuir.

---

## 📄 Licencia

[MIT](LICENSE) — úsalo, modifícalo y distribúyelo libremente.
