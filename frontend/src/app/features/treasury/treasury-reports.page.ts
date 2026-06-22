import { Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-treasury-reports-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, ReactiveFormsModule],
  template: `
    <section class="grid gap-4 lg:grid-cols-[1fr_0.8fr]">
      <mat-card class="government-card">
        <p class="section-eyebrow">Reportes</p>
        <h2 class="m-0 text-2xl font-black">Corte de pagos filtrado</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          Exporta pagos por rango de fechas, servicio, gateway, metodo y estatus.
        </p>

        <form class="mt-6 grid gap-4" [formGroup]="form">
          <div class="grid gap-4 md:grid-cols-2">
            <mat-form-field appearance="outline"><mat-label>Desde</mat-label><input matInput type="date" formControlName="from"></mat-form-field>
            <mat-form-field appearance="outline"><mat-label>Hasta</mat-label><input matInput type="date" formControlName="to"></mat-form-field>
          </div>
          <div class="grid gap-4 md:grid-cols-2">
            <mat-form-field appearance="outline">
              <mat-label>Servicio</mat-label>
              <mat-select formControlName="service_type">
                <mat-option value="">Todos</mat-option>
                <mat-option value="PREDIAL">Predial</mat-option>
                <mat-option value="WATER">Agua</mat-option>
                <mat-option value="TRAFFIC_FINE">Multas</mat-option>
              </mat-select>
            </mat-form-field>
            <mat-form-field appearance="outline">
              <mat-label>Estatus</mat-label>
              <mat-select formControlName="status">
                <mat-option value="">Todos</mat-option>
                <mat-option value="PENDING">Pendiente</mat-option>
                <mat-option value="PAID">Pagado</mat-option>
                <mat-option value="FAILED">Fallido</mat-option>
              </mat-select>
            </mat-form-field>
          </div>
          <div class="grid gap-4 md:grid-cols-2">
            <mat-form-field appearance="outline">
              <mat-label>Gateway</mat-label>
              <mat-select formControlName="gateway">
                <mat-option value="">Todos</mat-option>
                <mat-option value="openpay">OpenPay</mat-option>
                <mat-option value="stripe">Stripe</mat-option>
                <mat-option value="mercadopago">MercadoPago</mat-option>
              </mat-select>
            </mat-form-field>
            <mat-form-field appearance="outline">
              <mat-label>Metodo</mat-label>
              <mat-select formControlName="method">
                <mat-option value="">Todos</mat-option>
                <mat-option value="credit_card">Tarjeta credito</mat-option>
                <mat-option value="debit_card">Tarjeta debito</mat-option>
                <mat-option value="spei">SPEI</mat-option>
                <mat-option value="oxxo_cash">OXXO</mat-option>
              </mat-select>
            </mat-form-field>
          </div>
        </form>

        <div class="mt-6 flex flex-wrap gap-3">
          <button mat-flat-button color="primary" class="corporate-button" type="button" (click)="download('csv')">
            <mat-icon>download</mat-icon>
            CSV
          </button>
          <button mat-stroked-button class="corporate-button" type="button" (click)="download('pdf')">
            <mat-icon>picture_as_pdf</mat-icon>
            PDF
          </button>
        </div>
      </mat-card>

      <mat-card class="government-card">
        <p class="section-eyebrow">Incluye</p>
        <h2 class="m-0 text-2xl font-black">Campos exportados</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          Referencia, linea de captura, servicio, gateway, metodo, estatus, importe y fecha de pago.
        </p>
      </mat-card>
    </section>
  `,
})
export class TreasuryReportsPage {
  private readonly fb = inject(FormBuilder);
  private readonly auth = inject(AuthService);
  protected readonly form = this.fb.nonNullable.group({
    from: [''],
    to: [''],
    service_type: [''],
    gateway: [''],
    method: [''],
    status: [''],
  });

  protected download(format: 'csv' | 'pdf'): void {
    const query = new URLSearchParams(
      Object.entries(this.form.getRawValue()).filter(([, value]) => value !== '') as [string, string][],
    ).toString();
    const path = `/api/v1/treasury/reports/payments.${format}${query ? `?${query}` : ''}`;

    fetch(path, {
      headers: {
        Authorization: `Bearer ${this.auth.token() ?? ''}`,
      },
    })
      .then((response) => response.blob())
      .then((blob) => {
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `pagos.${format}`;
        anchor.click();
        URL.revokeObjectURL(url);
      });
  }
}
