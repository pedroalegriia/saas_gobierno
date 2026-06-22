import { Injectable, computed, inject, signal } from '@angular/core';
import { tap } from 'rxjs';
import { ApiClientService } from './api-client.service';

interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: 'super_admin' | 'treasury' | 'citizen';
}

interface LoginResponse {
  token_type: 'Bearer';
  access_token: string;
  expires_in: number | null;
  user: AuthUser;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly api = inject(ApiClientService);
  private readonly tokenSignal = signal<string | null>(localStorage.getItem('access_token'));
  private readonly userSignal = signal<AuthUser | null>(null);

  readonly token = this.tokenSignal.asReadonly();
  readonly user = this.userSignal.asReadonly();
  readonly isAuthenticated = computed(() => this.tokenSignal() !== null);

  login(email: string, password: string) {
    return this.api.post<LoginResponse>('/auth/login', { email, password }).pipe(
      tap((response) => {
        localStorage.setItem('access_token', response.access_token);
        this.tokenSignal.set(response.access_token);
        this.userSignal.set(response.user);
      }),
    );
  }

  logout(): void {
    localStorage.removeItem('access_token');
    this.tokenSignal.set(null);
    this.userSignal.set(null);
  }
}
