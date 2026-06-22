import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { AdminService, AdminUser } from '../../core/services/admin.service';
import { ToastService } from '../../core/services/toast.service';

@Component({
  selector: 'app-admin-users-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatInputModule, MatSelectModule, ReactiveFormsModule],
  template: `
    <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
      <mat-card class="government-card">
        <p class="section-eyebrow">Usuarios</p>
        <h2 class="m-0 text-2xl font-black">{{ form.controls.id.value ? 'Editar usuario' : 'Crear usuario' }}</h2>
        <form class="mt-6 grid gap-4" [formGroup]="form" (ngSubmit)="save()">
          <input type="hidden" formControlName="id">
          <mat-form-field appearance="outline"><mat-label>Nombre</mat-label><input matInput formControlName="name"></mat-form-field>
          <mat-form-field appearance="outline"><mat-label>Email</mat-label><input matInput formControlName="email"></mat-form-field>
          <mat-form-field appearance="outline"><mat-label>Password</mat-label><input matInput type="password" formControlName="password"></mat-form-field>
          <mat-form-field appearance="outline"><mat-label>ID Municipio</mat-label><input matInput type="number" formControlName="municipality_id"></mat-form-field>
          <div class="grid gap-4 md:grid-cols-2">
            <mat-form-field appearance="outline">
              <mat-label>Rol</mat-label>
              <mat-select formControlName="role">
                <mat-option value="super_admin">Super Admin</mat-option>
                <mat-option value="treasury">Tesoreria</mat-option>
                <mat-option value="cashier">Cajero</mat-option>
                <mat-option value="auditor">Auditor</mat-option>
                <mat-option value="citizen">Ciudadano</mat-option>
              </mat-select>
            </mat-form-field>
            <mat-form-field appearance="outline">
              <mat-label>Estatus</mat-label>
              <mat-select formControlName="status">
                <mat-option value="ACTIVE">Activo</mat-option>
                <mat-option value="INACTIVE">Inactivo</mat-option>
                <mat-option value="BLOCKED">Bloqueado</mat-option>
              </mat-select>
            </mat-form-field>
          </div>
          <button mat-flat-button color="primary" class="corporate-button" [disabled]="form.invalid">Guardar usuario</button>
        </form>
      </mat-card>

      <mat-card class="government-card">
        <p class="section-eyebrow">Listado</p>
        <h2 class="m-0 text-2xl font-black">Usuarios</h2>
        <div class="mt-6 grid gap-3">
          @for (user of users(); track user.id) {
            <button class="user-row text-left" type="button" (click)="edit(user)">
              <span>
                <strong>{{ user.name }}</strong>
                <small>{{ user.email }} · {{ user.role }}</small>
              </span>
              <span class="status-chip">{{ user.status }}</span>
            </button>
          }
        </div>
      </mat-card>
    </section>
  `,
  styles: [
    `
      .user-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 22px;
        padding: 16px;
        background: #f8fafc;
      }

      .user-row strong,
      .user-row small {
        display: block;
      }

      .user-row small {
        color: var(--color-muted);
      }
    `,
  ],
})
export class AdminUsersPage {
  private readonly fb = inject(FormBuilder);
  private readonly admin = inject(AdminService);
  private readonly toast = inject(ToastService);
  protected readonly users = signal<AdminUser[]>([]);
  protected readonly form = this.fb.nonNullable.group({
    id: this.fb.control<number | null>(null),
    municipality_id: this.fb.control<number | null>(1),
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['password', Validators.required],
    role: this.fb.nonNullable.control<AdminUser['role']>('treasury', Validators.required),
    status: this.fb.nonNullable.control<AdminUser['status']>('ACTIVE', Validators.required),
  });

  constructor() {
    this.load();
  }

  protected edit(user: AdminUser): void {
    this.form.patchValue({
      id: user.id ?? null,
      municipality_id: user.municipality_id,
      name: user.name,
      email: user.email,
      password: '',
      role: user.role,
      status: user.status,
    });
  }

  protected save(): void {
    const payload = this.form.getRawValue() as AdminUser;
    if (!payload.password) {
      delete payload.password;
    }

    this.admin.saveUser(payload).subscribe(() => {
      this.toast.success('Usuario guardado.');
      this.form.reset({ id: null, municipality_id: 1, name: '', email: '', password: 'password', role: 'treasury', status: 'ACTIVE' });
      this.load();
    });
  }

  private load(): void {
    this.admin.users().subscribe((response) => this.users.set(response.data));
  }
}
