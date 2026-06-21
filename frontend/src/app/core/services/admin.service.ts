import { Injectable, inject } from '@angular/core';
import { ApiClientService } from './api-client.service';

export interface AdminMunicipality {
  id?: number;
  name: string;
  slug: string;
  domain?: string | null;
  logo?: string | null;
  primary_color: string;
  secondary_color: string;
  status: 'ACTIVE' | 'INACTIVE' | 'SUSPENDED';
  settings?: Record<string, unknown>;
}

export interface AdminUser {
  id?: number;
  municipality_id: number | null;
  name: string;
  email: string;
  password?: string;
  role: 'super_admin' | 'treasury' | 'cashier' | 'auditor' | 'citizen';
  status: 'ACTIVE' | 'INACTIVE' | 'BLOCKED';
}

export interface AdminMetrics {
  municipalities: number;
  active_municipalities: number;
  users: number;
  monthly_revenue: string;
  pending_capture_lines: number;
  top_municipalities: Array<{ name: string; amount: number }>;
}

@Injectable({ providedIn: 'root' })
export class AdminService {
  private readonly api = inject(ApiClientService);

  metrics() {
    return this.api.get<AdminMetrics>('/super-admin/metrics');
  }

  municipalities() {
    return this.api.get<{ data: AdminMunicipality[] }>('/super-admin/municipalities');
  }

  saveMunicipality(payload: AdminMunicipality) {
    return payload.id
      ? this.api.put<{ data: AdminMunicipality }>(`/super-admin/municipalities/${payload.id}`, payload)
      : this.api.post<{ data: AdminMunicipality }>('/super-admin/municipalities', payload);
  }

  users() {
    return this.api.get<{ data: AdminUser[] }>('/super-admin/users');
  }

  saveUser(payload: AdminUser) {
    return payload.id
      ? this.api.put<{ data: AdminUser }>(`/super-admin/users/${payload.id}`, payload)
      : this.api.post<{ data: AdminUser }>('/super-admin/users', payload);
  }
}
