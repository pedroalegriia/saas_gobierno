import { Injectable, inject } from '@angular/core';
import { ApiClientService } from './api-client.service';

export interface TreasuryDashboard {
  kpis: Record<string, string | number>;
  charts: Record<string, unknown[]>;
  tables: Record<string, unknown[]>;
}

@Injectable({ providedIn: 'root' })
export class ReportsService {
  private readonly api = inject(ApiClientService);

  dashboard() {
    return this.api.get<TreasuryDashboard>('/treasury/dashboard');
  }
}
