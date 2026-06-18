import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-login-page',
  imports: [MatButtonModule, MatCardModule, MatFormFieldModule, MatInputModule, ReactiveFormsModule],
  template: `
    <main class="page login">
      <mat-card class="government-card">
        <p class="muted">Acceso seguro</p>
        <h1>Tesoreria municipal</h1>
        <form [formGroup]="form" (ngSubmit)="login()">
          <mat-form-field appearance="outline">
            <mat-label>Correo</mat-label>
            <input matInput type="email" formControlName="email" autocomplete="email">
          </mat-form-field>
          <mat-form-field appearance="outline">
            <mat-label>Contrasena</mat-label>
            <input matInput type="password" formControlName="password" autocomplete="current-password">
          </mat-form-field>
          <button mat-flat-button color="primary" type="submit" [disabled]="form.invalid || loading()">
            Entrar
          </button>
        </form>
        @if (message()) {
          <p class="message">{{ message() }}</p>
        }
      </mat-card>
    </main>
  `,
  styles: [
    `
      .login {
        display: grid;
        min-height: calc(100vh - 72px);
        place-items: center;
      }

      mat-card {
        width: min(100%, 440px);
      }

      form {
        display: grid;
        gap: 12px;
      }

      .message {
        color: #b91c1c;
        font-weight: 600;
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
