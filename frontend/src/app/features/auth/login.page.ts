import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-login-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, ReactiveFormsModule],
  template: `
    <main class="page login">
      <section class="login-shell">
        <aside class="hero-panel login-hero">
          <p class="section-eyebrow !text-white/80 before:!bg-white">Acceso institucional</p>
          <h1 class="m-0 text-4xl font-black leading-none tracking-tight sm:text-5xl">Tesoreria municipal</h1>
          <p class="mt-5 leading-7 text-white/76">
            Consulta recaudacion, pagos pendientes, recibos emitidos y reportes financieros desde un entorno seguro.
          </p>

          <div class="mt-8 grid gap-3">
            @for (item of securityItems; track item.label) {
              <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-3">
                <mat-icon>{{ item.icon }}</mat-icon>
                <span class="font-semibold text-white/84">{{ item.label }}</span>
              </div>
            }
          </div>
        </aside>

        <mat-card class="government-card login-card">
          <p class="section-eyebrow">Acceso seguro</p>
          <h2 class="m-0 text-3xl font-black">Iniciar sesion</h2>
          <p class="mt-3 leading-7 text-slate-500">
            Ingresa con una cuenta autorizada para el municipio activo.
          </p>

          <form class="form-shell mt-6" [formGroup]="form" (ngSubmit)="login()">
          <mat-form-field appearance="outline">
            <mat-label>Correo</mat-label>
            <input matInput type="email" formControlName="email" autocomplete="email">
          </mat-form-field>
          <mat-form-field appearance="outline">
            <mat-label>Contrasena</mat-label>
            <input matInput type="password" formControlName="password" autocomplete="current-password">
          </mat-form-field>
          <button mat-flat-button color="primary" class="corporate-button" type="submit" [disabled]="form.invalid || loading()">
            @if (loading()) {
              Validando...
            } @else {
              Entrar al dashboard
            }
          </button>
        </form>
        @if (message()) {
          <p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ message() }}</p>
        }
        </mat-card>
      </section>
    </main>
  `,
  styles: [
    `
      .login {
        display: grid;
        min-height: calc(100vh - 72px);
        place-items: center;
      }

      .login-shell {
        display: grid;
        width: min(100%, 1040px);
        gap: 18px;
      }

      .login-hero {
        min-height: 420px;
      }

      .login-card {
        align-self: center;
      }

      @media (min-width: 900px) {
        .login-shell {
          grid-template-columns: 1.08fr 0.92fr;
        }
      }
    `,
  ],
})
export class LoginPage {
  private readonly fb = inject(FormBuilder);
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);

  protected readonly loading = signal(false);
  protected readonly message = signal('');
  protected readonly securityItems = [
    { icon: 'admin_panel_settings', label: 'Roles y permisos por municipio' },
    { icon: 'vpn_key', label: 'Tokens Sanctum/JWT' },
    { icon: 'history', label: 'Auditoria de accesos y cambios' },
  ];
  protected readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required],
  });

  protected login(): void {
    this.loading.set(true);
    this.message.set('');
    this.auth.login(this.form.controls.email.value, this.form.controls.password.value).subscribe({
      next: () => {
        this.loading.set(false);
        void this.router.navigate(['/tesoreria']);
      },
      error: () => {
        this.loading.set(false);
        this.message.set('No fue posible iniciar sesion.');
      },
    });
  }
}
