import { NgTemplateOutlet } from '@angular/common';
import { Component, computed, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { PaymentBreakdown, ReportsService, TreasuryDashboard } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-payments-page',
  imports: [MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, NgTemplateOutlet],
  template: `
    <section class="grid gap-4 xl:grid-cols-2">
      <mat-card class="government-card">
        <p class="section-eyebrow">Tipos de pago</p>
        <h2 class="m-0 text-2xl font-black">Cobro por metodo</h2>
        <div class="mt-6 grid gap-4">
          @for (method of paymentMethods(); track method.key) {
            <ng-container [ngTemplateOutlet]="breakdownTpl" [ngTemplateOutletContext]="{ item: method, icon: paymentTypeIcon(method.key), gateway: false }" />
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
            <ng-container [ngTemplateOutlet]="breakdownTpl" [ngTemplateOutletContext]="{ item: gateway, icon: 'account_balance', gateway: true }" />
          } @empty {
            <p class="empty-state">No hay pagos confirmados por gateway.</p>
          }
        </div>
      </mat-card>
    </section>

    <mat-card class="government-card mt-4">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="section-eyebrow">Operacion</p>
          <h2 class="m-0 text-2xl font-black">Ultimos pagos</h2>
        </div>
        <span class="status-chip">Pagos</span>
      </div>
      <mat-form-field appearance="outline" class="mt-5">
        <mat-label>Filtrar por folio, referencia o servicio</mat-label>
        <input matInput (input)="query.set($any($event.target).value)">
      </mat-form-field>
      @if (!dashboard()) {
        <div class="mt-6 grid gap-3 md:grid-cols-2">
          <div class="skeleton h-24"></div>
          <div class="skeleton h-24"></div>
        </div>
      }
      <div class="mt-6 grid gap-3 md:grid-cols-2">
        @for (row of filteredPayments(); track row.reference ?? row.title) {
          <ng-container [ngTemplateOutlet]="rowTpl" [ngTemplateOutletContext]="{ row }" />
        } @empty {
          <p class="empty-state">Aun no hay pagos confirmados para este municipio.</p>
        }
      </div>
    </mat-card>

    <ng-template #breakdownTpl let-item="item" let-icon="icon" let-gateway="gateway">
      <div class="breakdown-row">
        <div class="breakdown-heading">
          <span class="breakdown-icon"><mat-icon>{{ icon }}</mat-icon></span>
          <div>
            <strong>{{ item.label }}</strong>
            <span>{{ item.count }} operaciones</span>
          </div>
        </div>
        <div class="breakdown-meta">
          <span>{{ money(item.amount) }}</span>
          <strong>{{ item.percentage }}%</strong>
        </div>
        <div class="breakdown-track" [class.gateway]="gateway">
          <span [style.width.%]="item.percentage"></span>
        </div>
      </div>
    </ng-template>

    <ng-template #rowTpl let-row="row">
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
    </ng-template>
  `,
  styles: [
    `
      .operation-row,
      .breakdown-row {
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 24px;
        padding: 16px;
        background: #f8fafc;
      }

      .operation-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
      }

      .operation-copy,
      .breakdown-heading {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
      }

      .operation-copy strong,
      .operation-copy span,
      .breakdown-heading strong,
      .breakdown-heading span {
        display: block;
      }

      .operation-copy span,
      .breakdown-heading span {
        overflow: hidden;
        max-width: 260px;
        color: var(--color-muted);
        font-size: 0.78rem;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .operation-icon,
      .breakdown-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 18px;
        background: #fff;
        color: var(--color-primary);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
      }

      .operation-amount,
      .breakdown-meta {
        font-weight: 900;
      }

      .breakdown-row {
        display: grid;
        gap: 12px;
      }

      .breakdown-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
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
    `,
  ],
})
export class TreasuryPaymentsPage {
  private readonly reports = inject(ReportsService);
  protected readonly dashboard = signal<TreasuryDashboard | null>(null);
  protected readonly paymentMethods = computed(() => this.dashboard()?.charts.payment_methods ?? []);
  protected readonly paymentGateways = computed(() => this.dashboard()?.charts.payment_gateways ?? []);
  protected readonly latestPayments = computed(() => this.dashboard()?.tables.latest_payments ?? []);
  protected readonly query = signal('');
  protected readonly filteredPayments = computed(() => {
    const normalized = this.query().trim().toLowerCase();
    if (!normalized) {
      return this.latestPayments();
    }

    return this.latestPayments().filter((row) =>
      row.title.toLowerCase().includes(normalized)
      || row.subtitle.toLowerCase().includes(normalized)
      || (row.reference ?? '').toLowerCase().includes(normalized),
    );
  });

  constructor() {
    this.reports.dashboard().subscribe((dashboard) => this.dashboard.set(dashboard));
  }

  protected money(amount: number): string {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount);
  }

  protected paymentTypeIcon(type: PaymentBreakdown['key']): string {
    const icons: Record<string, string> = {
      credit_card: 'credit_card',
      debit_card: 'payment',
      spei: 'account_balance',
      oxxo_cash: 'storefront',
    };

    return icons[type] ?? 'payments';
  }
}
