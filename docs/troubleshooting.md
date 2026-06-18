# Troubleshooting

## `getaddrinfo for mysql failed`

Este error aparece cuando ejecutas Artisan fuera de Docker con:

```dotenv
DB_HOST=mysql
```

`mysql` es el hostname del servicio dentro de Docker Compose. Si ejecutas
Laravel localmente en tu maquina, usa:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
```

Opciones:

### Ejecutar migraciones dentro de Docker

```bash
docker compose -f infra/docker-compose.yml exec api php artisan migrate --seed
```

### Ejecutar migraciones localmente

```bash
cp backend/.env.local.example backend/.env
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
```

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
