import { CurrencyPipe } from '@angular/common';
import { Component, inject, input, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { PaymentsService } from '../../core/services/payments.service';
import { Payment } from '../../core/models/payment.model';

@Component({
  selector: 'app-payment-page',
  imports: [CurrencyPipe, MatButtonModule, MatCardModule, MatFormFieldModule, MatInputModule, MatSelectModule, ReactiveFormsModule],
  template: `
    <main class="page">
      <section class="government-card">
        <p class="muted">Linea de captura</p>
        <h1>{{ folio() }}</h1>
        <p>Selecciona el metodo de pago. El gateway se resolvera por estrategia en el backend.</p>

        <form [formGroup]="form" (ngSubmit)="pay()">
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

          <mat-form-field appearance="outline">
            <mat-label>Token de pago</mat-label>
            <input matInput formControlName="paymentToken">
          </mat-form-field>

          <button mat-flat-button color="primary" type="submit" [disabled]="form.invalid || loading()">
            Pagar en linea
          </button>
        </form>
      </section>

      @if (payment(); as paid) {
        <section class="government-card receipt">
          <p class="muted">Pago registrado</p>
          <h2>{{ paid.amount | currency:'MXN':'symbol':'1.2-2' }}</h2>
          <p>Referencia: {{ paid.reference }}</p>
          <p>Estatus: {{ paid.status }}</p>
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
        word-break: break-word;
      }

      form {
        display: grid;
        gap: 12px;
        margin-top: 20px;
      }

      .receipt {
        margin-top: 18px;
      }

      .message {
        color: #b91c1c;
        font-weight: 600;
      }
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
