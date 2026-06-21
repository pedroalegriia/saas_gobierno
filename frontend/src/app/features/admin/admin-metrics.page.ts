import { Component, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { AdminMetrics, AdminService } from '../../core/services/admin.service';

@Component({
  selector: 'app-admin-metrics-page',
  imports: [MatCardModule],
  template: `
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
      @for (item of cards(); track item.label) {
        <mat-card class="government-card !min-h-0">
          <span class="text-sm font-bold uppercase tracking-[0.12em] text-slate-400">{{ item.label }}</span>
          <strong class="mt-3 block text-3xl font-black">{{ item.value }}</strong>
        </mat-card>
      }
    </section>

    <mat-card class="government-card mt-4">
      <p class="section-eyebrow">Top municipios</p>
      <h2 class="m-0 text-2xl font-black">Recaudacion global</h2>
      <div class="mt-5 grid gap-3">
        @for (row of metrics()?.top_municipalities ?? []; track row.name) {
          <div class="flex justify-between rounded-2xl bg-slate-50 p-4 font-bold">
            <span>{{ row.name }}</span>
            <span>{{ money(row.amount) }}</span>
          </div>
        }
      </div>
    </mat-card>
  `,
})
export class AdminMetricsPage {
  private readonly admin = inject(AdminService);
  protected readonly metrics = signal<AdminMetrics | null>(null);
  protected readonly cards = signal<Array<{ label: string; value: string | number }>>([]);

  constructor() {
    this.admin.metrics().subscribe((metrics) => {
      this.metrics.set(metrics);
      this.cards.set([
        { label: 'Municipios', value: metrics.municipalities },
        { label: 'Activos', value: metrics.active_municipalities },
        { label: 'Usuarios', value: metrics.users },
        { label: 'Recaudacion mensual', value: metrics.monthly_revenue },
        { label: 'Lineas pendientes', value: metrics.pending_capture_lines },
      ]);
    });
  }

  protected money(value: number): string {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
  }
}
