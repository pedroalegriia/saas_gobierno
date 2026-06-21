import { Component, computed, inject, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { ReportsService, TreasuryDashboard } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-receipts-page',
  imports: [MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule],
  template: `
    <mat-card class="government-card">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="section-eyebrow">Comprobantes</p>
          <h2 class="m-0 text-2xl font-black">Recibos emitidos</h2>
        </div>
        <span class="status-chip">PDF oficial</span>
      </div>
      <mat-form-field appearance="outline" class="mt-5">
        <mat-label>Filtrar recibos</mat-label>
        <input matInput (input)="query.set($any($event.target).value)">
      </mat-form-field>
      @if (!dashboard()) {
        <div class="mt-6 grid gap-3 md:grid-cols-2">
          <div class="skeleton h-24"></div>
          <div class="skeleton h-24"></div>
        </div>
      }

      <div class="mt-6 grid gap-3 md:grid-cols-2">
        @for (row of filteredReceipts(); track row.title) {
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
  `,
  styles: [
    `
      .operation-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 24px;
        padding: 16px;
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

      .operation-copy span {
        overflow: hidden;
        max-width: 320px;
        color: var(--color-muted);
        font-size: 0.82rem;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .operation-icon {
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

      .operation-amount {
        flex: 0 0 auto;
        font-weight: 900;
      }
    `,
  ],
})
export class TreasuryReceiptsPage {
  private readonly reports = inject(ReportsService);
  protected readonly dashboard = signal<TreasuryDashboard | null>(null);
  protected readonly issuedReceipts = computed(() => this.dashboard()?.tables.issued_receipts ?? []);
  protected readonly query = signal('');
  protected readonly filteredReceipts = computed(() => {
    const normalized = this.query().trim().toLowerCase();
    if (!normalized) {
      return this.issuedReceipts();
    }

    return this.issuedReceipts().filter((row) =>
      row.title.toLowerCase().includes(normalized)
      || row.subtitle.toLowerCase().includes(normalized),
    );
  });

  constructor() {
    this.reports.dashboard().subscribe((dashboard) => this.dashboard.set(dashboard));
  }
}
