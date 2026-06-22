import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-treasury-shell-page',
  imports: [MatIconModule, RouterLink, RouterLinkActive, RouterOutlet],
  template: `
    <main class="page space-y-6">
      <section class="hero-panel grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
        <div>
          <p class="section-eyebrow !text-white/80 before:!bg-white">Tesoreria</p>
          <h1 class="m-0 max-w-3xl text-4xl font-black leading-none tracking-tight sm:text-6xl">
            Operacion municipal
          </h1>
          <p class="mt-5 max-w-2xl leading-7 text-white/76">
            Administra recaudacion, lineas de captura, pagos, adeudos y recibos en secciones separadas.
          </p>
        </div>
        <div class="metric-pill min-w-56">
          <span class="block text-sm text-white/60">Corte operativo</span>
          <strong class="text-2xl font-black">Hoy</strong>
        </div>
      </section>

      <nav class="treasury-tabs" aria-label="Modulos de tesoreria">
        @for (item of tabs; track item.path) {
          <a
            [routerLink]="item.path"
            routerLinkActive="active"
            [routerLinkActiveOptions]="{ exact: true }"
          >
            <mat-icon>{{ item.icon }}</mat-icon>
            <span>{{ item.label }}</span>
          </a>
        }
      </nav>

      <router-outlet />
    </main>
  `,
  styles: [
    `
      .treasury-tabs {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 28px;
        padding: 10px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: var(--shadow-soft);
      }

      .treasury-tabs a {
        display: inline-flex;
        min-width: max-content;
        align-items: center;
        gap: 8px;
        border-radius: 20px;
        padding: 12px 16px;
        color: var(--color-muted);
        font-weight: 900;
        transition: 180ms ease;
      }

      .treasury-tabs a.active,
      .treasury-tabs a:hover {
        background: var(--color-primary);
        color: #fff;
      }

      .treasury-tabs mat-icon {
        width: 20px;
        height: 20px;
        font-size: 20px;
      }
    `,
  ],
})
export class TreasuryShellPage {
  protected readonly tabs = [
    { path: ['/tesoreria', 'resumen'], label: 'Resumen', icon: 'dashboard' },
    { path: ['/tesoreria', 'lineas-captura'], label: 'Lineas de captura', icon: 'qr_code_2' },
    { path: ['/tesoreria', 'pagos'], label: 'Pagos', icon: 'payments' },
    { path: ['/tesoreria', 'adeudos'], label: 'Adeudos', icon: 'pending_actions' },
    { path: ['/tesoreria', 'recibos'], label: 'Recibos', icon: 'receipt_long' },
    { path: ['/tesoreria', 'reportes'], label: 'Reportes', icon: 'download' },
  ];
}
