import { Component, computed, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { ReportsService, RevenueMonth, RevenuePoint, TreasuryDashboard } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-dashboard-page',
  imports: [MatCardModule, MatIconModule],
  template: `
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      @for (kpi of kpis(); track kpi.key) {
        <mat-card class="government-card kpi-card">
          <div class="flex items-start justify-between gap-4">
            <span class="kpi-icon"><mat-icon>{{ icon(kpi.key) }}</mat-icon></span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">Activo</span>
          </div>
          <span class="mt-5 block text-sm font-bold uppercase tracking-[0.12em] text-slate-400">{{ kpi.label }}</span>
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
          @for (point of revenueByDay(); track point.date) {
            <span [style.height.%]="barHeight(point)" [title]="point.label + ': ' + money(point.amount)">
              <small>{{ point.label }}</small>
            </span>
          }
        </div>
      </mat-card>

      <mat-card class="government-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="section-eyebrow">Historico</p>
            <h2 class="m-0 text-2xl font-black">Recaudacion mensual</h2>
          </div>
          <mat-icon class="text-[var(--color-primary)]">bar_chart</mat-icon>
        </div>
        <div class="chart-bars mt-8">
          @for (point of revenueByMonth(); track point.month) {
            <span [style.height.%]="monthBarHeight(point)" [title]="point.label + ': ' + money(point.amount)">
              <small>{{ point.label }}</small>
            </span>
          }
        </div>
      </mat-card>

      <mat-card class="government-card">
        <p class="section-eyebrow">Servicios</p>
        <h2 class="m-0 text-2xl font-black">Distribucion por servicio</h2>
        <div class="mt-6 grid gap-4">
          @for (service of serviceMix(); track service.service_type) {
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
        margin-top: 16px;
      }

      @media (min-width: 900px) {
        .analytics {
          grid-template-columns: 1.5fr 1fr 1fr;
        }
      }

      .chart-bars {
        display: flex;
        height: 220px;
        align-items: end;
        gap: 10px;
      }

      .chart-bars span {
        position: relative;
        flex: 1;
        min-height: 18%;
        border-radius: 999px 999px 10px 10px;
        background: linear-gradient(180deg, var(--color-secondary), var(--color-primary));
      }

      .chart-bars small {
        position: absolute;
        right: 50%;
        bottom: -24px;
        color: var(--color-muted);
        font-size: 0.68rem;
        font-weight: 800;
        transform: translateX(50%) rotate(-35deg);
        white-space: nowrap;
      }
    `,
  ],
})
export class TreasuryDashboardPage {
  private readonly reports = inject(ReportsService);
  protected readonly dashboard = signal<TreasuryDashboard | null>(null);
  protected readonly revenueByDay = computed(() => this.dashboard()?.charts.revenue_by_day ?? []);
  protected readonly revenueByMonth = computed(() => this.dashboard()?.charts.revenue_by_month ?? []);
  protected readonly serviceMix = computed(() => (this.dashboard()?.charts.distribution_by_service ?? []).map((service) => ({
    ...service,
    value: service.percentage,
  })));
  protected readonly kpis = computed(() => {
    const kpis = this.dashboard()?.kpis;

    if (!kpis) {
      return [];
    }

    return [
      { key: 'daily_revenue', label: 'Recaudacion del dia', value: kpis.daily_revenue },
      { key: 'monthly_revenue', label: 'Recaudacion mensual', value: kpis.monthly_revenue },
      { key: 'predial_collected', label: 'Predial cobrado', value: kpis.predial_collected },
      { key: 'water_collected', label: 'Agua cobrada', value: kpis.water_collected },
      { key: 'fines_collected', label: 'Multas cobradas', value: kpis.fines_collected },
      { key: 'pending_payments', label: 'Pagos pendientes', value: kpis.pending_payments },
    ];
  });

  constructor() {
    this.reports.dashboard().subscribe((dashboard) => this.dashboard.set(dashboard));
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

  protected barHeight(point: RevenuePoint): number {
    const max = Math.max(...this.revenueByDay().map((item) => item.amount), 1);

    return Math.max(18, Math.round((point.amount / max) * 100));
  }

  protected monthBarHeight(point: RevenueMonth): number {
    const max = Math.max(...this.revenueByMonth().map((item) => item.amount), 1);

    return Math.max(18, Math.round((point.amount / max) * 100));
  }

  protected money(amount: number): string {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount);
  }
}
