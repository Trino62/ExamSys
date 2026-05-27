# Contribuir a ExamSys

¡Gracias por tu interés en mejorar ExamSys! Aquí encontrarás todo lo que necesitas para contribuir.

---

## 📋 Tabla de contenidos

- [Reportar un bug](#-reportar-un-bug)
- [Sugerir una mejora](#-sugerir-una-mejora)
- [Configurar el entorno local](#-configurar-el-entorno-local)
- [Flujo de trabajo](#-flujo-de-trabajo)
- [Convenciones de código](#-convenciones-de-código)
- [Migraciones de base de datos](#-migraciones-de-base-de-datos)
- [Pull Request checklist](#-pull-request-checklist)

---

## 🐛 Reportar un bug

1. Busca en los [issues existentes](../../issues) para evitar duplicados.
2. Abre un nuevo issue con la etiqueta **bug** e incluye:
   - Descripción clara del problema
   - Pasos para reproducirlo
   - Comportamiento esperado vs. comportamiento actual
   - Versión de PHP y MySQL / MariaDB
   - Capturas de pantalla si aplica

---

## 💡 Sugerir una mejora

1. Abre un issue con la etiqueta **enhancement**.
2. Describe el caso de uso concreto que resuelve (¿quién lo necesita y por qué?).
3. Si tienes una propuesta de implementación, inclúyela — agiliza la revisión.

---

## ⚙️ Configurar el entorno local

### Con Docker (recomendado)

```bash
git clone https://github.com/tu-usuario/examsys.git
cd examsys
docker-compose up -d
```

Abre **http://localhost:8080**. La base de datos se inicializa automáticamente.

### Manual

**Requisitos:** PHP 8.1+ con `pdo_mysql`, MySQL 8.0+ (o MariaDB 10.6+), servidor web.

```bash
git clone https://github.com/tu-usuario/examsys.git
cd examsys

# 1. Crea el archivo de entorno
cp .env.example .env
# Edita .env con tus credenciales

# 2. Crea la base de datos y ejecuta migraciones
mysql -u root -p -e "CREATE DATABASE examen CHARACTER SET utf8mb4;"
mysql -u root -p examen < db/preguntas.sql
mysql -u root -p examen < db/migracion_resultados.sql
mysql -u root -p examen < db/migracion_template_slug.sql
mysql -u root -p examen < db/migracion_email.sql
mysql -u root -p examen < db/migracion_nota.sql
mysql -u root -p examen < db/migracion_templates.sql
mysql -u root -p examen < db/migracion_config.sql

# 3. Levanta el servidor de desarrollo
php -S localhost:8080
```

---

## 🔄 Flujo de trabajo

```bash
# 1. Haz fork del repositorio y clónalo
git clone https://github.com/TU-USUARIO/examsys.git

# 2. Crea una rama descriptiva a partir de main
git checkout -b fix/eliminar-resultado-null
git checkout -b feature/webhook-completar-examen

# 3. Haz tus cambios y commitea con mensajes claros
git commit -m "fix: evitar null reference al eliminar resultado"
git commit -m "feat: webhook configurable al completar examen"

# 4. Abre un Pull Request hacia main con descripción detallada
```

### Tipos de commit recomendados

| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Corrección de bug |
| `refactor:` | Mejora de código sin cambio de comportamiento |
| `style:` | Cambios de CSS / formato visual |
| `db:` | Nuevas migraciones o cambios de esquema |
| `docs:` | Documentación |

---

## 🖊️ Convenciones de código

### PHP
- Indentación con **4 espacios**
- Variables en `$camelCase`, constantes en `UPPER_SNAKE_CASE`
- Siempre usar **PDO con prepared statements** — nunca concatenar SQL con input del usuario
- Archivos en `server/` devuelven siempre `Content-Type: application/json`
- Todo endpoint admin verifica `$_SESSION['admin']` al inicio

### JavaScript
- Vanilla JS — sin frameworks ni dependencias externas
- `const` por defecto, `let` solo cuando el valor cambia; nunca `var`
- `fetch` + `Content-Type: application/json` para toda comunicación con el servidor
- Funciones con nombre descriptivo en `camelCase`

### CSS
- Sin frameworks — CSS vanilla dentro del `<style>` de cada página
- Variables de color principales: `#1d3759` (oscuro) / `#2b4f80` (medio) / `#f2f5f8` (fondo)
- Mobile-first; breakpoints con `max-width` cuando sea necesario

---

## 🗄️ Migraciones de base de datos

Cualquier cambio de esquema debe:

1. Crearse como un archivo nuevo en `db/` con el nombre `migracion_<descripcion>.sql`
2. Ser **idempotente** — usar `ADD COLUMN IF NOT EXISTS`, `CREATE TABLE IF NOT EXISTS`, etc.
3. Agregarse a la lista de migraciones en `install.php`
4. Incluirse en `db/preguntas.sql` y `docker/init.sql` si afecta la estructura base
5. Documentarse en el `README.md` (sección Variables de entorno o Estructura)

---

## ✅ Pull Request checklist

Antes de abrir tu PR, verifica:

- [ ] El código funciona localmente (Docker o manual)
- [ ] No se introducen consultas SQL con concatenación de strings
- [ ] Si hay nuevo endpoint en `server/`, está protegido si requiere admin
- [ ] Si hay cambio de esquema, se creó la migración correspondiente y se actualizó `install.php`
- [ ] El README refleja cualquier cambio visible para el usuario final
- [ ] No se commitea el archivo `.env`

---

## 📄 Licencia

Al contribuir aceptas que tu código se distribuirá bajo la [licencia MIT](LICENSE) del proyecto.
