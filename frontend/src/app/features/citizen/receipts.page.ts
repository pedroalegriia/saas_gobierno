import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';

@Component({
  selector: 'app-receipts-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, ReactiveFormsModule],
  template: `
    <main class="page grid gap-6 lg:grid-cols-[0.55fr_0.45fr] lg:items-center">
      <section class="hero-panel">
        <p class="section-eyebrow !text-white/80 before:!bg-white">Comprobantes</p>
        <h1 class="m-0 max-w-2xl text-4xl font-black leading-none tracking-tight sm:text-6xl">
          Recibos oficiales siempre disponibles.
        </h1>
        <p class="mt-5 max-w-xl leading-7 text-white/76">
          Descarga comprobantes emitidos por pagos confirmados y comparte el PDF cuando necesites validar tu tramite.
        </p>
      </section>

      <section class="government-card">
        <div class="mb-6 grid size-16 place-items-center rounded-3xl bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
          <mat-icon class="!size-9 !text-4xl">receipt_long</mat-icon>
        </div>
        <p class="section-eyebrow">Buscar recibo</p>
        <h2 class="m-0 text-3xl font-black">Ingresa tu folio</h2>
        <p class="mt-3 leading-7 text-slate-500">
          Puedes usar el folio de recibo o referencia de pago generada por el sistema.
        </p>

        <form class="form-shell mt-6" [formGroup]="form" (ngSubmit)="download()">
          <mat-form-field appearance="outline">
            <mat-label>Folio de recibo o pago</mat-label>
            <input matInput formControlName="folio">
          </mat-form-field>
          <button mat-flat-button color="primary" class="corporate-button" type="submit" [disabled]="form.invalid">
            Descargar recibo
          </button>
        </form>
        @if (message()) {
          <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-600">{{ message() }}</p>
        }
      </section>
    </main>
  `,
  styles: [
    `
    `,
  ],
})
export class ReceiptsPage {
  private readonly fb = inject(FormBuilder);
  protected readonly message = signal('');
  protected readonly form = this.fb.nonNullable.group({
    folio: ['', Validators.required],
  });

  protected download(): void {
    const folio = encodeURIComponent(this.form.controls.folio.value.trim());
    this.message.set('Abriendo recibo...');
    window.open(`/api/v1/receipts/${folio}`, '_blank', 'noopener');
  }
}
