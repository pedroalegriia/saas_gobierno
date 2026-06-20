import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-treasury-reports-page',
  imports: [MatButtonModule, MatCardModule, MatIconModule],
  template: `
    <section class="grid gap-4 lg:grid-cols-2">
      <mat-card class="government-card">
        <p class="section-eyebrow">Reportes</p>
        <h2 class="m-0 text-2xl font-black">Corte de pagos</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          Exporta los pagos del municipio con referencia, linea de captura, servicio, gateway, metodo, estatus e importe.
        </p>
        <button mat-flat-button color="primary" class="corporate-button mt-6" type="button" (click)="downloadPaymentsCsv()">
          <mat-icon>download</mat-icon>
          Descargar CSV de pagos
        </button>
      </mat-card>

      <mat-card class="government-card">
        <p class="section-eyebrow">Proximamente</p>
        <h2 class="m-0 text-2xl font-black">Excel y PDF</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          La estructura ya separa el modulo de reportes para agregar cortes diarios, conciliacion bancaria y formatos PDF oficiales.
        </p>
      </mat-card>
    </section>
  `,
})
export class TreasuryReportsPage {
  private readonly auth = inject(AuthService);

  protected downloadPaymentsCsv(): void {
    fetch('/api/v1/treasury/reports/payments.csv', {
      headers: {
        Authorization: `Bearer ${this.auth.token() ?? ''}`,
      },
    })
      .then((response) => response.blob())
      .then((blob) => {
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = 'pagos.csv';
        anchor.click();
        URL.revokeObjectURL(url);
      });
  }
}
