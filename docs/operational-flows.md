# Flujos operativos implementados

## Lineas de captura

- Los folios se reservan con la tabla `folio_sequences`.
- La llave de secuencia es `municipality_id + service_type + year`.
- La reserva usa bloqueo transaccional para evitar colisiones concurrentes.
- El comando `php artisan capture-lines:expire` vence lineas `PENDING` con
  `expiration_date` anterior al dia actual y falla pagos pendientes asociados.
- El PDF oficial se descarga desde `/api/v1/capture-lines/{folio}/pdf`.
- El PDF incluye branding municipal, QR al link de pago y codigo de barras
  visual del folio.

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

1. Busca `payments.reference`.
2. Marca el pago como `PAID`.
3. Marca la linea de captura como `PAID`.
4. Guarda el payload del webhook en `payments.metadata`.

## Recibos oficiales

- El endpoint `/api/v1/receipts/{folio}` devuelve un PDF oficial.
- El folio puede ser folio de recibo o referencia de pago.
- El PDF incluye branding municipal, QR de verificacion y codigo de barras
  visual del folio.

## Reportes

El endpoint protegido `/api/v1/treasury/reports/payments.csv` exporta pagos del
tenant en CSV. La autorizacion usa `TreasuryPolicy::exportReports`.

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
