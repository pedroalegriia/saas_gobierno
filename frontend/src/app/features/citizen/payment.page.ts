import { CurrencyPipe } from '@angular/common';
import { Component, inject, input, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { PaymentsService } from '../../core/services/payments.service';
import { Payment } from '../../core/models/payment.model';

@Component({
  selector: 'app-payment-page',
  imports: [CurrencyPipe, MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, ReactiveFormsModule],
  template: `
    <main class="page grid gap-6 lg:grid-cols-[0.62fr_0.38fr]">
      <section class="government-card">
        <p class="section-eyebrow">Pago en linea</p>
        <h1 class="m-0 break-words text-3xl font-black tracking-tight sm:text-5xl">{{ folio() }}</h1>
        <p class="mt-4 max-w-2xl leading-7 text-slate-500">
          Selecciona el gateway y metodo de pago. La API procesara la operacion mediante Strategy Pattern
          para mantener integraciones desacopladas.
        </p>

        <div class="mt-7 grid gap-3 sm:grid-cols-3">
          @for (badge of securityBadges; track badge.label) {
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <mat-icon class="text-[var(--color-primary)]">{{ badge.icon }}</mat-icon>
              <strong class="mt-2 block text-sm">{{ badge.label }}</strong>
            </div>
          }
        </div>

        <form class="form-shell mt-7" [formGroup]="form" (ngSubmit)="pay()">
          <div class="grid gap-4 md:grid-cols-2">
            <mat-form-field appearance="outline">
              <mat-label>Gateway</mat-label>
              <mat-select formControlName="gateway">
                <mat-option value="stripe">Stripe</mat-option>
                <mat-option value="openpay">OpenPay</mat-option>
                <mat-option value="mercadopago">MercadoPago</mat-option>
              </mat-select>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Metodo</mat-label>
              <mat-select formControlName="method">
                <mat-option value="credit_card">Tarjeta de credito</mat-option>
                <mat-option value="debit_card">Tarjeta de debito</mat-option>
                <mat-option value="spei">SPEI</mat-option>
              </mat-select>
            </mat-form-field>
          </div>

          <mat-form-field appearance="outline">
            <mat-label>Token de pago</mat-label>
            <input matInput formControlName="paymentToken">
          </mat-form-field>

          <button mat-flat-button color="primary" class="corporate-button" type="submit" [disabled]="form.invalid || loading()">
            @if (loading()) {
              Procesando...
            } @else {
              Pagar en linea
            }
          </button>
        </form>

        @if (message()) {
          <p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ message() }}</p>
        }
      </section>

      <aside class="government-card self-start lg:sticky lg:top-28">
        <div class="rounded-[28px] bg-slate-950 p-5 text-white">
          <p class="text-sm font-bold uppercase tracking-[0.14em] text-white/50">Resumen</p>
          <h2 class="mt-3 text-2xl font-black">Linea lista para pago</h2>
          <p class="mt-2 break-words text-sm text-white/70">{{ folio() }}</p>
        </div>

        @if (payment(); as paid) {
          <div class="mt-5">
            <span class="status-chip">Pago registrado</span>
            <h3 class="my-3 text-4xl font-black">{{ paid.amount | currency:'MXN':'symbol':'1.2-2' }}</h3>
            <dl class="grid gap-3 text-sm">
              <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Referencia</dt>
                <dd class="text-right font-extrabold">{{ paid.reference }}</dd>
              </div>
              <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Estatus</dt>
                <dd class="font-extrabold">{{ paid.status }}</dd>
              </div>
            </dl>
          </div>
        } @else {
          <p class="mt-5 leading-7 text-slate-500">
            Al confirmar, recibiras una referencia de pago y podras continuar al recibo oficial cuando el gateway confirme la operacion.
          </p>
        }
      </aside>
    </main>
  `,
  styles: [
    `
    `,
  ],
})
export class PaymentPage {
  readonly folio = input.required<string>();

  private readonly fb = inject(FormBuilder);
  private readonly payments = inject(PaymentsService);

  protected readonly loading = signal(false);
  protected readonly message = signal('');
  protected readonly payment = signal<Payment | null>(null);
  protected readonly securityBadges = [
    { icon: 'lock', label: 'Tokenizacion segura' },
    { icon: 'account_balance', label: 'Gateway certificado' },
    { icon: 'task_alt', label: 'Referencia trazable' },
  ];
  protected readonly form = this.fb.nonNullable.group({
    gateway: ['stripe', Validators.required],
    method: ['credit_card', Validators.required],
    paymentToken: ['tok_test', Validators.required],
  });

  protected pay(): void {
    this.loading.set(true);
    this.message.set('');

    this.payments.pay(
      this.folio(),
      this.form.controls.gateway.value,
      this.form.controls.method.value,
      this.form.controls.paymentToken.value,
    ).subscribe({
      next: (response) => {
        this.payment.set(response.data);
        this.loading.set(false);
      },
      error: () => {
        this.message.set('No fue posible procesar el pago.');
        this.loading.set(false);
      },
    });
  }
}
