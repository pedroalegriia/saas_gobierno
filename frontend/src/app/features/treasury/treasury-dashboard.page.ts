import { KeyValuePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { ReportsService, TreasuryDashboard } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-dashboard-page',
  imports: [KeyValuePipe, MatCardModule, MatIconModule, MatTableModule],
  template: `
    <main class="page space-y-6">
      <section class="hero-panel grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
        <div>
          <p class="section-eyebrow !text-white/80 before:!bg-white">Tesoreria</p>
          <h1 class="m-0 max-w-3xl text-4xl font-black leading-none tracking-tight sm:text-6xl">
            Dashboard de recaudacion
          </h1>
          <p class="mt-5 max-w-2xl leading-7 text-white/76">
            Monitorea ingresos, pagos pendientes y comportamiento por servicio en tiempo real.
          </p>
        </div>
        <div class="metric-pill min-w-56">
          <span class="block text-sm text-white/60">Corte operativo</span>
          <strong class="text-2xl font-black">Hoy</strong>
        </div>
      </section>

      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @for (kpi of dashboard()?.kpis | keyvalue; track kpi.key) {
          <mat-card class="government-card kpi-card">
            <div class="flex items-start justify-between gap-4">
              <span class="kpi-icon">
                <mat-icon>{{ icon(kpi.key) }}</mat-icon>
              </span>
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                Activo
              </span>
            </div>
            <span class="mt-5 block text-sm font-bold uppercase tracking-[0.12em] text-slate-400">{{ label(kpi.key) }}</span>
            <strong>{{ kpi.value }}</strong>
          </mat-card>
        }
      </section>

      <section class="analytics">
        <mat-card class="government-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="section-eyebrow">Analitica</p>
              <h2 class="m-0 text-2xl font-black">Recaudacion por dia</h2>
            </div>
            <mat-icon class="text-[var(--color-primary)]">show_chart</mat-icon>
          </div>
          <div class="chart-bars mt-8">
            @for (bar of bars; track $index) {
              <span [style.height.%]="bar"></span>
            }
          </div>
        </mat-card>
        <mat-card class="government-card">
          <p class="section-eyebrow">Servicios</p>
          <h2 class="m-0 text-2xl font-black">Distribucion por servicio</h2>
          <div class="mt-6 grid gap-4">
            @for (service of serviceMix; track service.label) {
              <div>
                <div class="mb-2 flex justify-between text-sm font-bold">
                  <span>{{ service.label }}</span>
                  <span>{{ service.value }}%</span>
                </div>
                <div class="h-3 rounded-full bg-slate-100">
                  <span class="block h-3 rounded-full bg-[var(--color-primary)]" [style.width.%]="service.value"></span>
                </div>
              </div>
            }
          </div>
        </mat-card>
      </section>

      <mat-card class="government-card">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="section-eyebrow">Operacion</p>
            <h2 class="m-0 text-2xl font-black">Ultimos movimientos</h2>
          </div>
          <span class="status-chip">Datos del tenant</span>
        </div>
        <div class="mt-6 grid gap-3">
          @for (row of activityRows; track row.title) {
            <div class="flex items-center justify-between gap-4 rounded-3xl border border-slate-100 bg-slate-50 p-4">
              <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-white text-[var(--color-primary)] shadow-sm">
                  <mat-icon>{{ row.icon }}</mat-icon>
                </span>
                <div>
                  <strong class="block">{{ row.title }}</strong>
                  <span class="text-sm text-slate-500">{{ row.subtitle }}</span>
                </div>
              </div>
              <span class="text-sm font-black text-slate-700">{{ row.amount }}</span>
            </div>
          }
        </div>
      </mat-card>
    </main>
  `,
  styles: [
    `
      .kpi-card {
        min-height: 178px;
      }

      .kpi-icon {
        display: grid;
        width: 52px;
        height: 52px;
        place-items: center;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(15, 76, 129, 0.12), rgba(176, 141, 87, 0.16));
        color: var(--color-primary);
      }

      .kpi-card strong {
        display: block;
        margin-top: 10px;
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        line-height: 1;
      }

      .analytics {
        display: grid;
        gap: 16px;
        margin: 18px 0;
      }

      @media (min-width: 900px) {
        .analytics {
          grid-template-columns: 2fr 1fr;
        }
      }

      .chart-bars {
        display: flex;
        height: 220px;
        align-items: end;
        gap: 10px;
      }

      .chart-bars span {
        flex: 1;
        min-height: 18%;
        border-radius: 999px 999px 10px 10px;
        background: linear-gradient(180deg, var(--color-secondary), var(--color-primary));
      }
    `,
  ],
})
export class TreasuryDashboardPage {
  private readonly reports = inject(ReportsService);
  protected readonly dashboard = signal<TreasuryDashboard | null>(null);
  protected readonly bars = [38, 52, 44, 72, 58, 88, 63, 76, 91, 69, 82, 94];
  protected readonly serviceMix = [
    { label: 'Predial', value: 48 },
    { label: 'Agua potable', value: 34 },
    { label: 'Multas', value: 18 },
  ];
  protected readonly activityRows = [
    { icon: 'payments', title: 'Pago predial confirmado', subtitle: 'Referencia COL-PRE-2026000001', amount: '$1,700.00' },
    { icon: 'water_drop', title: 'Adeudo de agua consultado', subtitle: 'Contrato AGU-COL-000123', amount: '$440.00' },
    { icon: 'receipt_long', title: 'Recibo emitido', subtitle: 'Disponible para descarga', amount: 'PDF' },
  ];

  constructor() {
    this.reports.dashboard().subscribe((dashboard) => this.dashboard.set(dashboard));
  }

  protected label(key: string): string {
    const labels: Record<string, string> = {
      daily_revenue: 'Recaudacion del dia',
      monthly_revenue: 'Recaudacion mensual',
      predial_collected: 'Predial cobrado',
      water_collected: 'Agua cobrada',
      fines_collected: 'Multas cobradas',
      pending_payments: 'Pagos pendientes',
    };

    return labels[key] ?? key;
  }

  protected icon(key: string): string {
    const icons: Record<string, string> = {
      daily_revenue: 'today',
      monthly_revenue: 'calendar_month',
      predial_collected: 'home_work',
      water_collected: 'water_drop',
      fines_collected: 'traffic',
      pending_payments: 'pending_actions',
    };

    return icons[key] ?? 'analytics';
  }
}
