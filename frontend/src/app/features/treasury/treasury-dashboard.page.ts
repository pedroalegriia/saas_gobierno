import { KeyValuePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatTableModule } from '@angular/material/table';
import { ReportsService, TreasuryDashboard } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-dashboard-page',
  imports: [KeyValuePipe, MatCardModule, MatTableModule],
  template: `
    <main class="page">
      <section class="header">
        <p class="muted">Tesoreria</p>
        <h1>Dashboard de recaudacion</h1>
      </section>

      <section class="card-grid dashboard">
        @for (kpi of dashboard()?.kpis | keyvalue; track kpi.key) {
          <mat-card class="government-card kpi-card">
            <span>{{ label(kpi.key) }}</span>
            <strong>{{ kpi.value }}</strong>
          </mat-card>
        }
      </section>

      <section class="analytics">
        <mat-card class="government-card">
          <h2>Recaudacion por dia</h2>
          <p class="muted">Espacio reservado para grafica temporal.</p>
        </mat-card>
        <mat-card class="government-card">
          <h2>Distribucion por servicio</h2>
          <p class="muted">Espacio reservado para grafica por predial, agua y multas.</p>
        </mat-card>
      </section>

      <mat-card class="government-card">
        <h2>Ultimos pagos</h2>
        <p class="muted">La tabla se alimentara con pagos confirmados del tenant.</p>
      </mat-card>
    </main>
  `,
  styles: [
    `
      .header h1 {
        margin-top: 0;
        font-size: clamp(2rem, 5vw, 3.25rem);
      }

      .kpi-card {
        min-height: 120px;
      }

      .kpi-card span {
        color: var(--color-muted);
      }

      .kpi-card strong {
        display: block;
        margin-top: 12px;
        font-size: 2rem;
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
    `,
  ],
})
export class TreasuryDashboardPage {
  private readonly reports = inject(ReportsService);
  protected readonly dashboard = signal<TreasuryDashboard | null>(null);

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
}
