import { Component, computed, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { PaymentBreakdown, ReportsService, RevenueMonth, RevenuePoint, TreasuryDashboard } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-dashboard-page',
  imports: [MatCardModule, MatIconModule],
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
        @for (kpi of kpis(); track kpi.key) {
          <mat-card class="government-card kpi-card">
            <div class="flex items-start justify-between gap-4">
              <span class="kpi-icon">
                <mat-icon>{{ icon(kpi.key) }}</mat-icon>
              </span>
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                Activo
              </span>
            </div>
            <span class="mt-5 block text-sm font-bold uppercase tracking-[0.12em] text-slate-400">{{ kpi.label }}</span>
            <strong>{{ kpi.value }}</strong>
          </mat-card>
        }
      </section>

      <section class="grid gap-4 xl:grid-cols-[1fr_1fr_1.2fr]">
        <mat-card class="government-card">
          <p class="section-eyebrow">Tipos de pago</p>
          <h2 class="m-0 text-2xl font-black">Cobro por metodo</h2>
          <div class="mt-6 grid gap-4">
            @for (method of paymentMethods(); track method.key) {
              <div class="breakdown-row">
                <div class="breakdown-heading">
                  <span class="breakdown-icon"><mat-icon>{{ paymentTypeIcon(method.key) }}</mat-icon></span>
                  <div>
                    <strong>{{ method.label }}</strong>
                    <span>{{ method.count }} operaciones</span>
                  </div>
                </div>
                <div class="breakdown-meta">
                  <span>{{ money(method.amount) }}</span>
                  <strong>{{ method.percentage }}%</strong>
                </div>
                <div class="breakdown-track">
                  <span [style.width.%]="method.percentage"></span>
                </div>
              </div>
            } @empty {
              <p class="empty-state">No hay pagos confirmados por metodo.</p>
            }
          </div>
        </mat-card>

        <mat-card class="government-card">
          <p class="section-eyebrow">Pasarelas</p>
          <h2 class="m-0 text-2xl font-black">Cobro por gateway</h2>
          <div class="mt-6 grid gap-4">
            @for (gateway of paymentGateways(); track gateway.key) {
              <div class="breakdown-row">
                <div class="breakdown-heading">
                  <span class="breakdown-icon"><mat-icon>account_balance</mat-icon></span>
                  <div>
                    <strong>{{ gateway.label }}</strong>
                    <span>{{ gateway.count }} operaciones</span>
                  </div>
                </div>
                <div class="breakdown-meta">
                  <span>{{ money(gateway.amount) }}</span>
                  <strong>{{ gateway.percentage }}%</strong>
                </div>
                <div class="breakdown-track gateway">
                  <span [style.width.%]="gateway.percentage"></span>
                </div>
              </div>
            } @empty {
              <p class="empty-state">No hay pagos confirmados por gateway.</p>
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
      </section>

      <section>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="section-eyebrow">Operacion diaria</p>
            <h2 class="m-0 text-3xl font-black">Movimientos separados</h2>
          </div>
          <p class="max-w-lg text-sm leading-6 text-slate-500">
            Pagos, adeudos y recibos se muestran en columnas independientes para no mezclar conceptos.
          </p>
        </div>
      </section>

      <section class="grid gap-4 xl:grid-cols-3">
        <mat-card class="government-card">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="section-eyebrow">Operacion</p>
              <h2 class="m-0 text-2xl font-black">Ultimos pagos</h2>
            </div>
            <span class="status-chip">Datos reales</span>
          </div>
          <div class="mt-6 grid gap-3">
            @for (row of latestPayments(); track row.reference ?? row.title) {
              <div class="operation-row">
                <div class="operation-copy">
                  <span class="operation-icon"><mat-icon>{{ row.icon }}</mat-icon></span>
                  <div>
                    <strong>{{ row.title }}</strong>
                    <span>{{ row.subtitle }}</span>
                  </div>
                </div>
                <span class="operation-amount">{{ row.amount }}</span>
              </div>
            } @empty {
              <p class="empty-state">Aun no hay pagos confirmados para este municipio.</p>
            }
          </div>
        </mat-card>

        <mat-card class="government-card">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="section-eyebrow">Cartera</p>
              <h2 class="m-0 text-2xl font-black">Adeudos recientes</h2>
            </div>
            <span class="status-chip">Tenant</span>
          </div>
          <div class="mt-6 grid gap-3">
            @for (row of recentDebts(); track row.subtitle) {
              <div class="operation-row">
                <div class="operation-copy">
                  <span class="operation-icon"><mat-icon>{{ row.icon }}</mat-icon></span>
                  <div>
                    <strong>{{ row.title }}</strong>
                    <span>{{ row.subtitle }}</span>
                  </div>
                </div>
                <span class="operation-amount">{{ row.amount }}</span>
              </div>
            } @empty {
              <p class="empty-state">No hay adeudos pendientes cargados.</p>
            }
          </div>
        </mat-card>

        <mat-card class="government-card">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="section-eyebrow">Comprobantes</p>
              <h2 class="m-0 text-2xl font-black">Recibos emitidos</h2>
            </div>
            <span class="status-chip">PDF</span>
          </div>
          <div class="mt-6 grid gap-3">
            @for (row of issuedReceipts(); track row.title) {
              <div class="operation-row">
                <div class="operation-copy">
                  <span class="operation-icon"><mat-icon>{{ row.icon }}</mat-icon></span>
                  <div>
                    <strong>{{ row.title }}</strong>
                    <span>{{ row.subtitle }}</span>
                  </div>
                </div>
                <span class="operation-amount">{{ row.amount }}</span>
              </div>
            } @empty {
              <p class="empty-state">Aun no hay recibos emitidos.</p>
            }
          </div>
        </mat-card>
      </section>
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

      .operation-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 24px;
        padding: 14px;
        background: #f8fafc;
      }

      .operation-copy {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
      }

      .operation-copy strong,
      .operation-copy span {
        display: block;
      }

      .operation-copy strong {
        font-size: 0.92rem;
      }

      .operation-copy span {
        overflow: hidden;
        max-width: 220px;
        color: var(--color-muted);
        font-size: 0.78rem;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .operation-icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 18px;
        background: #fff;
        color: var(--color-primary);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
      }

      .operation-amount {
        flex: 0 0 auto;
        font-size: 0.86rem;
        font-weight: 900;
      }

      .breakdown-row {
        display: grid;
        gap: 12px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 24px;
        padding: 16px;
        background: #f8fafc;
      }

      .breakdown-heading {
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .breakdown-heading strong,
      .breakdown-heading span {
        display: block;
      }

      .breakdown-heading span {
        color: var(--color-muted);
        font-size: 0.78rem;
        font-weight: 700;
      }

      .breakdown-icon {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        border-radius: 18px;
        background: #fff;
        color: var(--color-primary);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
      }

      .breakdown-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-weight: 900;
      }

      .breakdown-meta span {
        color: #0f172a;
      }

      .breakdown-meta strong {
        color: var(--color-primary);
      }

      .breakdown-track {
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
      }

      .breakdown-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
      }

      .breakdown-track.gateway span {
        background: linear-gradient(90deg, #0f172a, var(--color-primary));
      }

      .empty-state {
        border-radius: 20px;
        margin: 0;
        padding: 18px;
        background: #f8fafc;
        color: var(--color-muted);
        font-weight: 700;
      }
    `,
  ],
})
export class TreasuryDashboardPage {
  private readonly reports = inject(ReportsService);
  protected readonly dashboard = signal<TreasuryDashboard | null>(null);
  protected readonly revenueByDay = computed(() => this.dashboard()?.charts.revenue_by_day ?? []);
  protected readonly revenueByMonth = computed(() => this.dashboard()?.charts.revenue_by_month ?? []);
  protected readonly paymentMethods = computed(() => this.dashboard()?.charts.payment_methods ?? []);
  protected readonly paymentGateways = computed(() => this.dashboard()?.charts.payment_gateways ?? []);
  protected readonly serviceMix = computed(() => (this.dashboard()?.charts.distribution_by_service ?? []).map((service) => ({
    ...service,
    value: service.percentage,
  })));
  protected readonly latestPayments = computed(() => this.dashboard()?.tables.latest_payments ?? []);
  protected readonly recentDebts = computed(() => this.dashboard()?.tables.recent_debts ?? []);
  protected readonly issuedReceipts = computed(() => this.dashboard()?.tables.issued_receipts ?? []);
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

  protected paymentTypeIcon(type: PaymentBreakdown['key']): string {
    const icons: Record<string, string> = {
      credit_card: 'credit_card',
      debit_card: 'payment',
      spei: 'account_balance',
    };

    return icons[type] ?? 'payments';
  }
}
