import { CurrencyPipe } from '@angular/common';
import { Component, computed, inject, input, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { Observable } from 'rxjs';
import { PaymentsService } from '../../core/services/payments.service';
import { DebtSummary, ServiceType } from '../../core/models/payment.model';

type DebtSearchResponse = { data: DebtSummary | DebtSummary[] };

@Component({
  selector: 'app-debt-search-page',
  imports: [CurrencyPipe, MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, ReactiveFormsModule],
  template: `
    <main class="page grid gap-6 lg:grid-cols-[0.72fr_0.28fr]">
      <section class="government-card">
        <p class="section-eyebrow">Consulta ciudadana</p>
        <div class="grid gap-6 lg:grid-cols-[1fr_0.8fr] lg:items-center">
          <div>
            <h1 class="m-0 text-3xl font-black tracking-tight sm:text-5xl">{{ title() }}</h1>
            <p class="mt-4 max-w-xl leading-7 text-slate-500">
              Ingresa tus datos para consultar adeudos vigentes y generar una linea de captura oficial.
            </p>
          </div>

          <div class="rounded-[28px] bg-slate-950 p-5 text-white shadow-2xl">
            <span class="status-chip !bg-white/10 !text-white">Proceso seguro</span>
            <div class="mt-5 grid gap-3">
              @for (step of flow; track step) {
                <div class="flex items-center gap-3 rounded-2xl bg-white/8 p-3">
                  <span class="grid size-8 place-items-center rounded-xl bg-white/12 text-sm font-black">{{ $index + 1 }}</span>
                  <span class="text-sm font-semibold text-white/84">{{ step }}</span>
                </div>
              }
            </div>
          </div>
        </div>

        <form class="form-shell mt-7" [formGroup]="form" (ngSubmit)="search()">
          <mat-form-field appearance="outline">
            <mat-label>{{ label() }}</mat-label>
            <input matInput formControlName="query" autocomplete="off">
          </mat-form-field>
          <button mat-flat-button color="primary" class="corporate-button" type="submit" [disabled]="form.invalid || loading()">
            @if (loading()) {
              Consultando...
            } @else {
              Consultar adeudo
            }
          </button>
        </form>

        @if (message()) {
          <p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ message() }}</p>
        }
      </section>

      @if (result(); as debt) {
        <section class="government-card result lg:sticky lg:top-28">
          <span class="status-chip">Adeudo encontrado</span>
          <p class="mt-6 text-sm font-bold uppercase tracking-[0.14em] text-slate-400">Importe total</p>
          <h2 class="my-2 text-4xl font-black text-slate-950">
            {{ amount(debt) | currency:'MXN':'symbol':'1.2-2' }}
          </h2>
          <div class="soft-divider"></div>
          <dl class="grid gap-3 text-sm">
            <div class="flex items-center justify-between">
              <dt class="text-slate-500">Estatus</dt>
              <dd class="font-extrabold">{{ debt.status }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-slate-500">Servicio</dt>
              <dd class="font-extrabold">{{ title() }}</dd>
            </div>
          </dl>
          <button mat-flat-button color="accent" class="corporate-button mt-6 w-full" (click)="createCaptureLine(debt)">
            Generar linea de captura
          </button>
        </section>
      } @else {
        <aside class="government-card lg:sticky lg:top-28">
          <mat-icon class="!size-12 !text-5xl text-[var(--color-primary)]">support_agent</mat-icon>
          <h2 class="mt-4 text-2xl font-black">Asistencia municipal</h2>
          <p class="mt-2 leading-7 text-slate-500">
            Verifica que el dato coincida con tu boleta, contrato o folio. Tus datos se consultan dentro del municipio activo.
          </p>
        </aside>
      }
    </main>
  `,
  styles: [
    `
      .result {
        align-self: start;
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
  protected readonly flow = ['Buscar', 'Validar adeudo', 'Generar folio', 'Pagar en linea'];

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
    const request: Observable<DebtSearchResponse> = this.service() === 'predial'
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
