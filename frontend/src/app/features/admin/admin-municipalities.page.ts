import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { AdminMunicipality, AdminService } from '../../core/services/admin.service';
import { ToastService } from '../../core/services/toast.service';

@Component({
  selector: 'app-admin-municipalities-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatInputModule, MatSelectModule, ReactiveFormsModule],
  template: `
    <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
      <mat-card class="government-card">
        <p class="section-eyebrow">Municipios</p>
        <h2 class="m-0 text-2xl font-black">{{ form.controls.id.value ? 'Editar municipio' : 'Crear municipio' }}</h2>
        <form class="mt-6 grid gap-4" [formGroup]="form" (ngSubmit)="save()">
          <input type="hidden" formControlName="id">
          <mat-form-field appearance="outline"><mat-label>Nombre</mat-label><input matInput formControlName="name"></mat-form-field>
          <mat-form-field appearance="outline"><mat-label>Slug</mat-label><input matInput formControlName="slug"></mat-form-field>
          <mat-form-field appearance="outline"><mat-label>Dominio personalizado</mat-label><input matInput formControlName="domain"></mat-form-field>
          <div class="grid gap-4 md:grid-cols-2">
            <mat-form-field appearance="outline"><mat-label>Color primario</mat-label><input matInput formControlName="primary_color"></mat-form-field>
            <mat-form-field appearance="outline"><mat-label>Color secundario</mat-label><input matInput formControlName="secondary_color"></mat-form-field>
          </div>
          <mat-form-field appearance="outline">
            <mat-label>Estatus</mat-label>
            <mat-select formControlName="status">
              <mat-option value="ACTIVE">Activo</mat-option>
              <mat-option value="INACTIVE">Inactivo</mat-option>
              <mat-option value="SUSPENDED">Suspendido</mat-option>
            </mat-select>
          </mat-form-field>
          <button mat-flat-button color="primary" class="corporate-button" [disabled]="form.invalid">Guardar</button>
        </form>
      </mat-card>

      <mat-card class="government-card">
        <p class="section-eyebrow">Listado</p>
        <h2 class="m-0 text-2xl font-black">Municipios configurados</h2>
        <div class="mt-6 grid gap-3">
          @for (municipality of municipalities(); track municipality.id) {
            <button class="municipality-row text-left" type="button" (click)="edit(municipality)">
              <span>
                <strong>{{ municipality.name }}</strong>
                <small>{{ municipality.slug }} · {{ municipality.domain ?? 'sin dominio' }}</small>
              </span>
              <span class="status-chip">{{ municipality.status }}</span>
            </button>
          }
        </div>
      </mat-card>
    </section>
  `,
  styles: [
    `
      .municipality-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 22px;
        padding: 16px;
        background: #f8fafc;
      }

      .municipality-row strong,
      .municipality-row small {
        display: block;
      }

      .municipality-row small {
        color: var(--color-muted);
      }
    `,
  ],
})
export class AdminMunicipalitiesPage {
  private readonly fb = inject(FormBuilder);
  private readonly admin = inject(AdminService);
  private readonly toast = inject(ToastService);
  protected readonly municipalities = signal<AdminMunicipality[]>([]);
  protected readonly form = this.fb.nonNullable.group({
    id: this.fb.control<number | null>(null),
    name: ['', Validators.required],
    slug: ['', Validators.required],
    domain: [''],
    primary_color: ['#0F4C81', Validators.required],
    secondary_color: ['#B08D57', Validators.required],
    status: this.fb.nonNullable.control<AdminMunicipality['status']>('ACTIVE', Validators.required),
  });

  constructor() {
    this.load();
  }

  protected edit(municipality: AdminMunicipality): void {
    this.form.patchValue({
      id: municipality.id ?? null,
      name: municipality.name,
      slug: municipality.slug,
      domain: municipality.domain ?? '',
      primary_color: municipality.primary_color,
      secondary_color: municipality.secondary_color,
      status: municipality.status,
    });
  }

  protected save(): void {
    this.admin.saveMunicipality(this.form.getRawValue() as AdminMunicipality).subscribe(() => {
      this.toast.success('Municipio guardado.');
      this.form.reset({ id: null, name: '', slug: '', domain: '', primary_color: '#0F4C81', secondary_color: '#B08D57', status: 'ACTIVE' });
      this.load();
    });
  }

  private load(): void {
    this.admin.municipalities().subscribe((response) => this.municipalities.set(response.data));
  }
}
