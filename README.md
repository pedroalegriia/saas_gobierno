# SaaS Gobierno Municipal

Plataforma SaaS multi-tenant para ayuntamientos de Mexico enfocada en pagos
en linea de predial, agua potable y multas de transito.

Esta primera fase deja una base de arquitectura, modelo de datos, estructura
de carpetas, contratos de dominio, migraciones, API REST documentada y frontend
Angular base para continuar el desarrollo por modulos.

## Stack objetivo

- Backend: Laravel 12, PHP 8.4+, MySQL 8, Sanctum, Queues, Notifications,
  Events, Policies, API Resources, Form Requests y PHPUnit.
- Frontend: Angular 20, Angular Material, Tailwind CSS, RxJS, Signals,
  Standalone Components, Routing, JWT, PWA y diseno Mobile First.
- Infraestructura opcional: Docker, Nginx, Redis y Supervisor.

## Estructura

```text
backend/        API Laravel con arquitectura limpia y modulos DDD
frontend/       SPA Angular para portal ciudadano y dashboard tesoreria
docs/           Arquitectura, modelo de datos, ER y OpenAPI
infra/          Docker, Nginx y Supervisor (opcional)
```

## Documentacion principal

- [Arquitectura](docs/architecture.md)
- [Modelo de datos y diagrama ER](docs/database.md)
- [OpenAPI](docs/openapi.yaml)
- [Guia UI/UX corporativa](docs/ui-ux-guidelines.md)
- [Troubleshooting](docs/troubleshooting.md)

## Fases sugeridas

1. Arquitectura, modelo de datos y estructura de carpetas.
2. Autenticacion, tenants, roles, auditoria y seguridad transversal.
3. Modulos Predial, Agua y Multas.
4. Lineas de captura, pagos, webhooks y recibos.
5. Dashboard, reportes, exportaciones y hardening productivo.

## Puesta en marcha local

El flujo recomendado para desarrollo y pruebas locales no usa Docker.

Requisitos locales:

- PHP 8.4+
- Composer
- MySQL 8
- Node 22+
- npm

### Backend

```bash
cp backend/.env.example backend/.env
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

El backend local queda en `http://localhost:8000`.

Asegurate de tener creada la base de datos local antes de migrar:

```sql
CREATE DATABASE saas_gobierno CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

La configuracion local por defecto es:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_gobierno
DB_USERNAME=root
DB_PASSWORD=
LOCAL_TENANT_SLUG=colima
```

En local, las peticiones hechas desde `localhost` se resuelven al municipio demo
configurado en `LOCAL_TENANT_SLUG`. Con los seeders puedes probar tesoreria con:

```text
Email: tesoreria@colima.gob.mx
Password: password
```

### Frontend

```bash
cd frontend
npm install
npm run start
```

El frontend local queda en `http://localhost:4200`. El dev server usa
`frontend/proxy.conf.json` para reenviar `/api/*` al backend en
`http://localhost:8000`, por ejemplo:

```text
http://localhost:4200/api/v1/auth/login -> http://localhost:8000/api/v1/auth/login
```

## Docker

Docker queda como opcion secundaria para mas adelante. Si decides usarlo,
parte de `backend/.env.docker.example`:

```bash
cp backend/.env.docker.example backend/.env
docker compose -f infra/docker-compose.yml up -d --build
docker compose -f infra/docker-compose.yml exec api composer install
docker compose -f infra/docker-compose.yml exec api php artisan migrate --seed
```

En Docker, `DB_HOST=mysql` es correcto porque `mysql` es el nombre del servicio
dentro de la red de Docker Compose. Para desarrollo local, usa `DB_HOST=127.0.0.1`.