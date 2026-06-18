# Guia UI/UX corporativa

## Direccion visual

La interfaz debe comunicar confianza institucional, claridad financiera y
rapidez operativa. El estilo combina:

- Paleta por tenant mediante variables CSS.
- Tarjetas blancas translúcidas sobre fondos suaves.
- Hero panels con gradientes sobrios.
- Badges de confianza y estados visibles.
- Jerarquia tipografica fuerte para montos, folios y acciones.
- Mobile First con grids que escalan a dashboard de escritorio.

## Tailwind

El frontend usa Tailwind CSS 4 sobre Angular 20 mediante PostCSS:

```text
frontend/.postcssrc.json
frontend/src/styles.scss
```

Las clases Tailwind se usan para layout, espaciado, responsive y composicion
rapida. Los componentes repetibles siguen viviendo como clases globales:

- `.hero-panel`
- `.government-card`
- `.section-eyebrow`
- `.corporate-button`
- `.trust-badge`
- `.metric-pill`
- `.form-shell`
- `.status-chip`

## Vistas redisenadas

- Portal ciudadano.
- Consulta de adeudos.
- Pago en linea.
- Recibos.
- Login de tesoreria.
- Dashboard de recaudacion.

## Siguientes mejoras recomendadas

1. Crear componentes compartidos:
   - `ServiceCardComponent`
   - `KpiCardComponent`
   - `PageHeaderComponent`
   - `EmptyStateComponent`
   - `ReceiptPreviewComponent`
2. Agregar skeleton loaders para consultas y dashboard.
3. Incorporar charts reales con datos del backend.
4. Crear tema oscuro opcional para tesoreria.
5. Implementar branding por municipio con logo y favicon dinamico.
6. Validar accesibilidad WCAG: contraste, focus states y navegacion teclado.
