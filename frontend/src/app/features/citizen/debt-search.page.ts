import { CurrencyPipe } from '@angular/common';
import { Component, computed, inject, input, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { PaymentsService } from '../../core/services/payments.service';
import { DebtSummary, ServiceType } from '../../core/models/payment.model';

@Component({
  selector: 'app-debt-search-page',
  imports: [CurrencyPipe, MatButtonModule, MatCardModule, MatFormFieldModule, MatInputModule, ReactiveFormsModule],
  template: `
    <main class="page">
      <section class="government-card">
        <p class="muted">Buscar</p>
        <h1>{{ title() }}</h1>

        <form [formGroup]="form" (ngSubmit)="search()">
          <mat-form-field appearance="outline">
            <mat-label>{{ label() }}</mat-label>
            <input matInput formControlName="query" autocomplete="off">
          </mat-form-field>
          <button mat-flat-button color="primary" type="submit" [disabled]="form.invalid || loading()">
            Consultar adeudo
          </button>
        </form>
      </section>

      @if (result(); as debt) {
        <section class="government-card result">
          <p class="muted">Adeudo encontrado</p>
          <h2>{{ amount(debt) | currency:'MXN':'symbol':'1.2-2' }}</h2>
          <p>Estatus: {{ debt.status }}</p>
          <button mat-flat-button color="accent" (click)="createCaptureLine(debt)">
            Generar linea de captura
          </button>
        </section>
      }

      @if (message()) {
        <p class="message">{{ message() }}</p>
      }
    </main>
  `,
  styles: [
    `
      h1 {
        margin-top: 0;
      }

      form {
        display: grid;
        gap: 12px;
      }

      mat-form-field {
        width: 100%;
      }

      .result {
        margin-top: 18px;
      }

      .message {
        color: #b91c1c;
        font-weight: 600;
      }
    `,
  ],
})
export class DebtSearchPage {
  readonly service = input.required<string>();

  private readonly fb = inject(FormBuilder);
  private readonly payments = inject(PaymentsService);
  private readonly router = inject(Router);

  protected readonly form = this.fb.nonNullable.group({
    query: ['', [Validators.required, Validators.minLength(3)]],
  });
  protected readonly result = signal<DebtSummary | null>(null);
  protected readonly loading = signal(false);
  protected readonly message = signal('');

  protected readonly title = computed(() => {
    const titles: Record<string, string> = {
      predial: 'Consulta de Predial',
      agua: 'Consulta de Agua Potable',
      multas: 'Consulta de Multas de Transito',
    };

    return titles[this.service()] ?? 'Consulta de adeudos';
  });

  protected readonly label = computed(() => {
    const labels: Record<string, string> = {
      predial: 'Clave catastral',
      agua: 'Numero de contrato',
      multas: 'Folio o placa',
    };

    return labels[this.service()] ?? 'Dato de busqueda';
  });

  protected search(): void {
    this.loading.set(true);
    this.message.set('');
    const query = this.form.controls.query.value.trim();
    const request = this.service() === 'predial'
      ? this.payments.searchPredial(query)
      : this.service() === 'agua'
        ? this.payments.searchWater(query)
        : this.payments.searchTrafficFines({ plate: query });

    request.subscribe({
      next: (response) => {
        const data = Array.isArray(response.data) ? response.data[0] : response.data as DebtSummary | undefined;
        this.result.set(data ?? null);
        this.message.set(data ? '' : 'No se encontraron adeudos.');
        this.loading.set(false);
      },
      error: () => {
        this.result.set(null);
        this.message.set('No fue posible consultar el adeudo.');
        this.loading.set(false);
      },
    });
  }

  protected createCaptureLine(debt: DebtSummary): void {
    this.payments.createCaptureLine(this.serviceType(), debt.id).subscribe({
      next: (response) => void this.router.navigate(['/pagar', response.data.folio]),
      error: () => this.message.set('No fue posible generar la linea de captura.'),
    });
  }

  protected amount(debt: DebtSummary): number {
    return Number(debt.total_balance ?? debt.amount ?? 0);
  }

  private serviceType(): ServiceType {
    return this.service() === 'predial' ? 'PREDIAL' : this.service() === 'agua' ? 'WATER' : 'TRAFFIC_FINE';
  }
}
