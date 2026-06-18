# SaaS Gobierno Municipal

Plataforma SaaS multi-tenant para ayuntamientos de Mexico enfocada en pagos
en linea de predial, agua potable y multas de transito.

Esta primera fase deja una base de arquitectura, modelo de datos, estructura
de carpetas, contratos de dominio, migraciones, API REST documentada, frontend
Angular base e infraestructura Docker para continuar el desarrollo por modulos.

## Stack objetivo

- Backend: Laravel 12, PHP 8.4+, MySQL 8, Sanctum, Queues, Notifications,
  Events, Policies, API Resources, Form Requests y PHPUnit.
- Frontend: Angular 20, Angular Material, RxJS, Signals, Standalone
  Components, Routing, JWT, PWA y diseno Mobile First.
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

## Fases sugeridas

1. Arquitectura, modelo de datos y estructura de carpetas.
2. Autenticacion, tenants, roles, auditoria y seguridad transversal.
3. Modulos Predial, Agua y Multas.
4. Lineas de captura, pagos, webhooks y recibos.
5. Dashboard, reportes, exportaciones y hardening productivo.

## Puesta en marcha esperada

La maquina de este agente no incluye PHP, Composer ni Docker. En un entorno
con esas herramientas instaladas:

```bash
cp backend/.env.example backend/.env
docker compose -f infra/docker-compose.yml up -d --build
docker compose -f infra/docker-compose.yml exec api composer install
docker compose -f infra/docker-compose.yml exec api php artisan migrate --seed
cd frontend && npm install && npm run start
```