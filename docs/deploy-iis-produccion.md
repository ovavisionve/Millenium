# Millennium — despliegue IIS (producción)

Checklist orientado a Windows Server + IIS + PHP (Laravel). Ajustá rutas y nombres de pool según tu servidor.

## 1. Sitio y carpeta física

- **Ruta física del sitio** debe apuntar a `...\millennium\public`, no a la raíz del repo (así no se expone `.env`, `composer.json`, etc.).
- **Documento predeterminado**: `index.php` (ya contemplado en `public/web.config`).

## 2. URL Rewrite

- Instalá **IIS URL Rewrite Module** (Microsoft).
- El archivo `public/web.config` ya incluye la regla que envía todo a `index.php` salvo archivos y carpetas reales.

## 3. Permisos NTFS (resumido)

| Ruta (relativa al repo) | Usuario típico | Permiso |
|-------------------------|-----------------|---------|
| `storage\` y `bootstrap\cache\` | Identidad del **application pool** (p. ej. `IIS AppPool\Millennium`) o `IIS_IUSRS` según política | **Modificar** (o al menos escritura en `storage\logs`, `storage\framework`, `bootstrap\cache`) |
| Resto del código | Solo lectura para el pool | Lectura |

Sin escritura en `storage` y `bootstrap\cache`, fallan logs, sesiones, vistas compiladas y `php artisan config:cache`.

## 4. PHP

- Versión compatible con `composer.json` (Laravel 11/12).
- Extensiones habituales: `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`.

## 5. Laravel en producción (CLI en el servidor)

Desde la **raíz del proyecto** (donde está `artisan`), con `.env` de producción:

```bat
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Tras cambiar `.env` o código de `config/`, volvé a ejecutar `config:cache` (o `php artisan config:clear` si necesitás depurar).

**Programador de tareas** (colas `database`, scheduler): configurá `php artisan schedule:run` cada minuto y un worker de cola según la carga.

## 6. Variables `.env` críticas

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://tu-dominio` (coherente con HTTPS si usás certificado)

## 7. Seeders

- `DatabaseSeeder` en **production** no crea usuarios `@millennium.local` ni ejecuta `MunicipiosParroquiasSeeder` (set reducido que chocaría con el catálogo nacional).
- Sí ejecuta **categorías y bancos** (`updateOrCreate`) para poder arrancar catálogos de negocio; si ya existen, no los duplica.
- **Primer usuario administrador en production**: crearlo manualmente (`php artisan tinker`, registro interno o migración dedicada), no depender del seeder de demo.
- Geografía completa: importación SQL o `millennium:import-geografia-dumps` con rutas a los dumps.
- Los seeders `Local*` siguen limitados a `APP_ENV=local`.

## 8. Respaldo

- Base de datos y archivos (`storage/app`) según política interna.
- No dejar bundles `.bundle` ni dumps `.sql` bajo `wwwroot` si no hace falta servirlos.
