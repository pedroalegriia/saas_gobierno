import { Injectable, signal, inject } from '@angular/core';
import { tap } from 'rxjs';
import { Municipality } from '../models/municipality.model';
import { ApiClientService } from './api-client.service';

@Injectable({ providedIn: 'root' })
export class TenantService {
  private readonly api = inject(ApiClientService);
  private readonly tenantSignal = signal<Municipality | null>(null);

  readonly tenant = this.tenantSignal.asReadonly();

  loadTenant() {
    return this.api.get<{ data?: Municipality } | Municipality>('/tenant').pipe(
      tap((response) => {
        const tenant = 'data' in response && response.data ? response.data : response;
        this.tenantSignal.set(tenant as Municipality);
        document.documentElement.style.setProperty('--color-primary', (tenant as Municipality).primary_color);
        document.documentElement.style.setProperty('--color-secondary', (tenant as Municipality).secondary_color);
      }),
    );
  }
}
