# Arquitectura de la plataforma

## Vision

La plataforma es un SaaS multi-tenant para ayuntamientos mexicanos. Cada
municipio opera como un tenant aislado logicamente por `municipality_id`, con
resolucion automatica por subdominio o dominio personalizado.

El diseno separa reglas de negocio, casos de uso, adaptadores externos y capa
HTTP para facilitar pruebas, cambios regulatorios, nuevos gateways de pago y
crecimiento por municipio.

## Principios

- Clean Architecture: dependencias hacia el dominio, nunca hacia frameworks.
- DDD modular: cada bounded context concentra lenguaje, reglas y contratos.
- SOLID: dependencias por interfaces, servicios pequenos y reemplazables.
- Mobile First: flujos ciudadanos optimizados para consulta y pago rapido.
- Seguridad por defecto: tenant scope, roles, auditoria, rate limiting y logs.
- Observabilidad: auditoria funcional, logs de acceso, pagos y cambios.

## Monorepo

```text
backend/
├── src/
│   ├── Shared/
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Presentation/
│   ├── Tenants/
│   ├── Predial/
│   ├── Water/
│   ├── TrafficFines/
│   ├── CaptureLines/
│   ├── Payments/
│   ├── Receipts/
│   ├── Reports/
│   └── Users/
├── database/
├── routes/
└── tests/

frontend/
├── src/app/core/
├── src/app/features/
├── src/app/layouts/
└── src/styles/

docs/
└── openapi.yaml

infra/
├── docker-compose.yml
├── nginx/
└── supervisor/
```

## Bounded contexts

### Shared

Contratos transversales, Value Objects, excepciones, enums, middleware comun,
auditoria y utilidades sin dependencia de un modulo especifico.

### Tenants

Gestiona municipios, resolucion por host, configuracion visual, estatus y
settings operativos.

### Users

Autenticacion, usuarios, roles, permisos, policies, tokens Sanctum/JWT y
sesiones administrativas.

### Predial, Water y TrafficFines

Modulos de consulta de adeudos, historial, generacion de lineas de captura y
transicion a pago.

### CaptureLines

Genera folios unicos por municipio, servicio y ejercicio fiscal:

```text
COL-PRE-2026000001
COL-AGU-2026000001
COL-MUL-2026000001
```

### Payments

Integra gateways mediante Strategy Pattern. La aplicacion depende de
`PaymentGatewayInterface`; OpenPay, MercadoPago y Stripe son adaptadores.

### Receipts

Genera comprobantes oficiales PDF despues de confirmar pago, con datos del
municipio, referencia, concepto e importe.

### Reports

Consulta y exporta recaudacion diaria, mensual, por servicio, por fechas y
adeudos pendientes en CSV, Excel y PDF.

## Flujo ciudadano

```text
Resolver tenant por host
        ↓
Seleccionar servicio
        ↓
Buscar cuenta, contrato o multa
        ↓
Consultar adeudo
        ↓
Generar linea de captura
        ↓
Procesar pago por gateway
        ↓
Confirmar webhook
        ↓
Emitir recibo PDF
```

## Flujo tesoreria

```text
Autenticacion
        ↓
Policy por rol y tenant
        ↓
Dashboard KPIs
        ↓
Consulta de pagos, adeudos y recibos
        ↓
Exportacion de reportes
```

## Resolucion multi-tenant

1. El middleware lee `Host`.
2. Busca coincidencia exacta en `municipalities.domain`.
3. Si no existe, extrae el subdominio y busca `municipalities.slug`.
4. Rechaza municipios inactivos.
5. Guarda el tenant resuelto en request container y aplica scopes por
   `municipality_id`.

## Seguridad

- Sanctum para APIs protegidas.
- Tokens JWT para el frontend.
- Rate limiting por IP, tenant y endpoint sensible.
- CORS restringido por dominios de municipio.
- CSRF para flujos stateful.
- Policies para Super Admin, Tesoreria y Ciudadano.
- `audit_logs` para cambios de datos sensibles.
- Logs separados para acceso, pagos y webhooks.

## Patrones clave

- Repository Pattern para persistencia.
- Use Cases para operaciones de aplicacion.
- DTOs para entrada/salida entre capas.
- Value Objects para dinero, folios, host y colores.
- Strategy Pattern para gateways de pago.
- Events & Listeners para pagos confirmados y recibos emitidos.
- API Resources y Form Requests en la capa Presentation.

## Convenciones

- PSR-12 en backend.
- Standalone Components en Angular.
- Servicios Angular con Signals para estado local.
- Componentes presentacionales sin conocimiento de APIs.
- Nombres de tablas en plural y claves foraneas explicitas.
