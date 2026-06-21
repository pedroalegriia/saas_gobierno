# Flujos operativos implementados

## Lineas de captura

- Los folios se reservan con la tabla `folio_sequences`.
- La llave de secuencia es `municipality_id + service_type + year`.
- La reserva usa bloqueo transaccional para evitar colisiones concurrentes.
- El comando `php artisan capture-lines:expire` vence lineas `PENDING` con
  `expiration_date` anterior al dia actual y falla pagos pendientes asociados.
- El PDF oficial se descarga desde `/api/v1/capture-lines/{folio}/pdf`.
- El PDF incluye branding municipal, matriz QR local/offline al link de pago y
  codigo de barras visual del folio.

## Tesoreria

La navegacion de Tesoreria esta separada por rutas:

- `/tesoreria/resumen`
- `/tesoreria/lineas-captura`
- `/tesoreria/pagos`
- `/tesoreria/adeudos`
- `/tesoreria/recibos`
- `/tesoreria/reportes`

## OpenPay / OXXO

Desde Tesoreria se puede generar una linea de captura y una referencia OXXO.
La referencia se registra como pago `PENDING` en `payments` con:

```text
gateway=openpay
method=oxxo_cash
status=PENDING
```

Cuando el webhook confirma el pago, el backend:

1. Registra el evento en `payment_webhook_events`.
2. Busca `payments.reference`.
3. Marca el pago como `PAID`.
4. Marca la linea de captura como `PAID`.
5. Guarda el payload del webhook en `payments.metadata`.
6. Emite automaticamente un recibo si todavia no existe.
7. Actualiza el evento webhook como `PROCESSED` o `FAILED`.

OpenPay puede operar en dos modos:

```dotenv
OPENPAY_ENABLED=false # fallback local/simulado
OPENPAY_ENABLED=true  # llamadas REST reales a OpenPay
```

Para modo real se requiere extension PHP `curl` y estas variables:

```dotenv
OPENPAY_MERCHANT_ID=
OPENPAY_PRIVATE_KEY=
OPENPAY_PUBLIC_KEY=
OPENPAY_WEBHOOK_SECRET=
```

Cuando `OPENPAY_WEBHOOK_SECRET` tiene valor, el webhook valida firma HMAC SHA-256
contra el body crudo usando los headers `X-OpenPay-Signature`,
`X-Openpay-Signature` u `OpenPay-Signature`.

## Recibos oficiales

- El endpoint `/api/v1/receipts/{folio}` devuelve un PDF oficial.
- El folio puede ser folio de recibo o referencia de pago.
- El PDF incluye branding municipal, matriz QR local/offline de verificacion y codigo de barras
  visual del folio.
- Cada descarga/reimpresion registra auditoria `receipt.reprinted`.

## Auditoria

Se registran eventos funcionales en `audit_logs`:

- `auth.login_success`
- `auth.login_failed`
- `auth.login_denied`
- `capture_line.generated`
- `capture_line.document_viewed`
- `capture_line.pdf_reprinted`
- `receipt.reprinted`

## Reportes

El endpoint protegido `/api/v1/treasury/reports/payments.csv` exporta pagos del
tenant en CSV. La autorizacion usa `TreasuryPolicy::exportReports`.

Tambien existe `/api/v1/treasury/reports/payments.pdf`. Ambos aceptan filtros:

```text
from, to, service_type, gateway, method, status
```

## Super Admin

El modulo `/admin` permite:

- Consultar metricas globales SaaS.
- Crear y editar municipios, branding, dominio y estatus.
- Crear y editar usuarios con rol y estatus.

Endpoints:

- `/api/v1/super-admin/metrics`
- `/api/v1/super-admin/municipalities`
- `/api/v1/super-admin/users`

## UX local

- El portal ciudadano guarda historial reciente en `localStorage`.
- Desde el historial se puede reintentar un pago o abrir el recibo.
- El pago exitoso permite compartir referencia por WhatsApp.
- Tesoreria incluye filtros locales en pagos, adeudos y recibos.
- Las listas muestran skeleton loaders durante carga inicial.

## Roles

- `super_admin`: acceso global.
- `treasury`: dashboard, lineas, pagos, reportes.
- `cashier`: generacion de lineas de captura.
- `auditor`: dashboard y reportes.
