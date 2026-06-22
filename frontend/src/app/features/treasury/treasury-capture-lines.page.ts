import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { ReportsService, TreasuryCaptureLineResponse } from '../../core/services/reports.service';

@Component({
  selector: 'app-treasury-capture-lines-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatSelectModule, ReactiveFormsModule],
  template: `
    <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
      <mat-card class="government-card">
        <p class="section-eyebrow">Atencion en caja</p>
        <h2 class="m-0 text-2xl font-black">Generar linea de captura</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          Busca por clave catastral, contrato, folio de multa o placa. Entrega documento impreso o link de pago.
        </p>

        <form class="mt-6 grid gap-4" [formGroup]="form" (ngSubmit)="generate()">
          <mat-form-field appearance="outline">
            <mat-label>Servicio</mat-label>
            <mat-select formControlName="service_type">
              <mat-option value="PREDIAL">Predial</mat-option>
              <mat-option value="WATER">Agua potable</mat-option>
              <mat-option value="TRAFFIC_FINE">Multas de transito</mat-option>
            </mat-select>
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>{{ lookupLabel() }}</mat-label>
            <input matInput formControlName="lookup" autocomplete="off">
          </mat-form-field>

          <button mat-flat-button color="primary" class="corporate-button" type="submit" [disabled]="form.invalid || loading()">
            @if (loading()) {
              Generando...
            } @else {
              Generar linea y referencia OXXO
            }
          </button>
        </form>

        @if (message()) {
          <p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ message() }}</p>
        }
      </mat-card>

      <mat-card class="government-card">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="section-eyebrow">Entrega al contribuyente</p>
            <h2 class="m-0 text-2xl font-black">Documento, link y OXXO</h2>
          </div>
          <span class="status-chip">OpenPay</span>
        </div>

        @if (result(); as data) {
          <div class="mt-6 grid gap-4">
            <div class="rounded-3xl bg-slate-950 p-5 text-white">
              <span class="text-sm font-bold uppercase tracking-[0.14em] text-white/50">Folio</span>
              <strong class="mt-2 block break-words text-3xl">{{ data.capture_line.folio }}</strong>
              <p class="mt-2 text-white/70">{{ data.citizen.name }} · {{ data.citizen.reference }}</p>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
              <a mat-flat-button color="primary" class="corporate-button" [href]="data.delivery.document_url" target="_blank" rel="noopener">
                Abrir documento
              </a>
              <button mat-stroked-button class="corporate-button" type="button" (click)="copy(data.delivery.payment_link)">
                Copiar link de pago
              </button>
            </div>

            @if (data.openpay) {
              <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <p class="m-0 text-sm font-black uppercase tracking-[0.14em] text-amber-700">Referencia OXXO / OpenPay</p>
                <strong class="mt-2 block text-2xl text-slate-950">{{ data.openpay.paynet_reference }}</strong>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ data.openpay.instructions }}</p>
                <div class="mt-4 grid gap-2 text-sm">
                  <span><strong>Monto:</strong> {{ money(+data.openpay.amount) }}</span>
                  <span><strong>Vence:</strong> {{ data.openpay.expires_at }}</span>
                  <span><strong>Referencia interna:</strong> {{ data.openpay.reference }}</span>
                </div>
              </div>
            }
          </div>
        } @else {
          <p class="empty-state mt-6">
            Aun no se ha generado una linea. Al crearla veras aqui el documento imprimible, link de pago y referencia OXXO.
          </p>
        }
      </mat-card>
    </section>
  `,
})
export class TreasuryCaptureLinesPage {
  private readonly fb = inject(FormBuilder);
  private readonly reports = inject(ReportsService);
  protected readonly result = signal<TreasuryCaptureLineResponse | null>(null);
  protected readonly loading = signal(false);
  protected readonly message = signal('');
  protected readonly form = this.fb.nonNullable.group({
    service_type: ['PREDIAL', Validators.required],
    lookup: ['', [Validators.required, Validators.minLength(3)]],
    include_oxxo_reference: [true],
  });

  protected lookupLabel(): string {
    const labels: Record<string, string> = {
      PREDIAL: 'Clave catastral',
      WATER: 'Numero de contrato',
      TRAFFIC_FINE: 'Folio o placa',
    };

    return labels[this.form.controls.service_type.value] ?? 'Dato de busqueda';
  }

  protected generate(): void {
    this.loading.set(true);
    this.message.set('');

    this.reports.createTreasuryCaptureLine({
      service_type: this.form.controls.service_type.value as 'PREDIAL' | 'WATER' | 'TRAFFIC_FINE',
      lookup: this.form.controls.lookup.value.trim(),
      include_oxxo_reference: this.form.controls.include_oxxo_reference.value,
    }).subscribe({
      next: (response) => {
        this.result.set(response);
        this.loading.set(false);
      },
      error: () => {
        this.message.set('No fue posible generar la linea de captura. Verifica el dato capturado.');
        this.loading.set(false);
      },
    });
  }

  protected copy(value: string): void {
    void navigator.clipboard?.writeText(value);
  }

  protected money(amount: number): string {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount);
  }
}
