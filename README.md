# SaaS Gobierno Municipal

Plataforma SaaS multi-tenant para ayuntamientos de Mexico enfocada en pagos
en linea de predial, agua potable y multas de transito.

Esta primera fase deja una base de arquitectura, modelo de datos, estructura
de carpetas, contratos de dominio, migraciones, API REST documentada, frontend
Angular base e infraestructura Docker para continuar el desarrollo por modulos.

## Stack objetivo

- Backend: Laravel 12, PHP 8.4+, MySQL 8, Sanctum, Queues, Notifications,
  Events, Policies, API Resources, Form Requests y PHPUnit.
- Frontend: Angular 20, Angular Material, Tailwind CSS, RxJS, Signals,
  Standalone Components, Routing, JWT, PWA y diseno Mobile First.
- Infraestructura: Docker, Nginx, Redis y Supervisor.

## Estructura

```text
backend/        API Laravel con arquitectura limpia y modulos DDD
frontend/       SPA Angular para portal ciudadano y dashboard tesoreria
docs/           Arquitectura, modelo de datos, ER y OpenAPI
infra/          Docker, Nginx y Supervisor
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

## Puesta en marcha esperada

La maquina de este agente no incluye PHP, Composer ni Docker. En un entorno
con esas herramientas instaladas:

### Opcion Docker

```bash
cp backend/.env.example backend/.env
docker compose -f infra/docker-compose.yml up -d --build
docker compose -f infra/docker-compose.yml exec api composer install
docker compose -f infra/docker-compose.yml exec api php artisan migrate --seed
cd frontend && npm install && npm run start
```

En Docker, `DB_HOST=mysql` es correcto porque `mysql` es el nombre del servicio
dentro de la red de Docker Compose.

### Opcion local sin Docker

Si ejecutas `php artisan migrate` directamente desde tu maquina, no uses
`DB_HOST=mysql`; ese hostname solo existe dentro de Docker. Usa el ejemplo local:

```bash
cp backend/.env.local.example backend/.env
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Asegurate de tener MySQL corriendo localmente y de que estos valores coincidan
con tu instalacion:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_gobierno
DB_USERNAME=root
DB_PASSWORD=
```

Si ves un error como `getaddrinfo for mysql failed`, significa que estas
ejecutando Artisan fuera de Docker con un `.env` configurado para Docker.