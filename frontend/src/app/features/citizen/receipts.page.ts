import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

@Component({
  selector: 'app-receipts-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatInputModule, ReactiveFormsModule],
  template: `
    <main class="page">
      <section class="government-card">
        <p class="muted">Comprobantes</p>
        <h1>Descarga tu recibo oficial</h1>
        <form [formGroup]="form" (ngSubmit)="download()">
          <mat-form-field appearance="outline">
            <mat-label>Folio de recibo o pago</mat-label>
            <input matInput formControlName="folio">
          </mat-form-field>
          <button mat-flat-button color="primary" type="submit" [disabled]="form.invalid">
            Descargar recibo
          </button>
        </form>
        @if (message()) {
          <p class="muted">{{ message() }}</p>
        }
      </section>
    </main>
  `,
  styles: [
    `
      form {
        display: grid;
        gap: 12px;
      }
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
