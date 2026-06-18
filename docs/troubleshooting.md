# Troubleshooting

## `getaddrinfo for mysql failed`

Este error aparece cuando ejecutas Artisan localmente con una configuracion de
Docker:

```dotenv
DB_HOST=mysql
```

`mysql` es el hostname del servicio dentro de Docker Compose. Para desarrollo
local usa:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
```

### Ejecutar migraciones localmente

```bash
cp backend/.env.example backend/.env
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
```

Antes de migrar, crea la base de datos:

```sql
CREATE DATABASE saas_gobierno CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Si en algun momento decides usar Docker, usa `backend/.env.docker.example`.

## `Target class [files] does not exist`

Verifica que tu rama incluya:

- `Illuminate\Filesystem\FilesystemServiceProvider::class`
- `backend/config/filesystems.php`
- `backend/bootstrap/cache/.gitkeep`

## `Target class [cache.store] does not exist`

Verifica que tu rama incluya:

- `Illuminate\Cache\CacheServiceProvider::class`
- `backend/config/cache.php`

## `There are no commands defined in the "package" namespace`

Verifica que tu rama incluya:

- `Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class`

## `Target class [db] does not exist`

Verifica que tu rama incluya:

- `Illuminate\Database\DatabaseServiceProvider::class`
