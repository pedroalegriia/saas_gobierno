import { Component, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { CitizenHistoryService } from '../../core/services/citizen-history.service';

@Component({
  selector: 'app-citizen-history-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, RouterLink],
  template: `
    <main class="page space-y-6">
      <section class="government-card">
        <p class="section-eyebrow">Historial ciudadano</p>
        <h1 class="m-0 text-3xl font-black sm:text-5xl">Tus pagos recientes</h1>
        <p class="mt-3 leading-7 text-slate-500">
          Historial guardado en este navegador para reintentar pagos, descargar recibos o compartir referencias.
        </p>
        <mat-form-field appearance="outline" class="mt-6">
          <mat-label>Buscar folio o referencia</mat-label>
          <input matInput (input)="query.set($any($event.target).value)">
        </mat-form-field>
      </section>

      <section class="grid gap-4 md:grid-cols-2">
        @for (item of filtered(); track item.folio) {
          <mat-card class="government-card !min-h-0">
            <div class="flex items-start justify-between gap-4">
              <span class="grid size-12 place-items-center rounded-2xl bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                <mat-icon>payments</mat-icon>
              </span>
              <span class="status-chip">{{ item.status }}</span>
            </div>
            <h2 class="mt-5 break-words text-2xl font-black">{{ item.folio }}</h2>
            <p class="text-slate-500">Referencia: {{ item.paymentReference ?? 'Pendiente' }}</p>
            <p class="text-slate-500">Importe: {{ item.amount ?? 'N/A' }}</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
              <a mat-flat-button color="primary" class="corporate-button" [routerLink]="['/pagar', item.folio]">
                Reintentar pago
              </a>
              <a mat-stroked-button class="corporate-button" [href]="'/api/v1/receipts/' + (item.paymentReference ?? item.folio)" target="_blank" rel="noopener">
                Recibo
              </a>
            </div>
          </mat-card>
        } @empty {
          <mat-card class="government-card text-center">
            <mat-icon class="!size-14 !text-6xl text-[var(--color-primary)]">history</mat-icon>
            <h2 class="mt-4 text-2xl font-black">Sin historial en este navegador</h2>
            <p class="text-slate-500">Cuando generes o pagues una linea, aparecera aqui.</p>
            <a mat-flat-button color="primary" class="corporate-button mt-4" routerLink="/">Ir al portal</a>
          </mat-card>
        }
      </section>
    </main>
  `,
})
export class CitizenHistoryPage {
  private readonly history = inject(CitizenHistoryService);
  protected readonly query = signal('');
  protected readonly filtered = computed(() => this.history.search(this.query()));
}
