import { Injectable, inject } from '@angular/core';
import { ApiClientService } from './api-client.service';

export interface TreasuryDashboard {
  municipality_id: number;
  kpis: TreasuryKpis;
  charts: {
    revenue_by_day: RevenuePoint[];
    revenue_by_month: RevenueMonth[];
    distribution_by_service: ServiceDistribution[];
    payment_methods: PaymentBreakdown[];
    payment_gateways: PaymentBreakdown[];
  };
  tables: {
    latest_payments: DashboardRow[];
    recent_debts: DashboardRow[];
    issued_receipts: DashboardRow[];
  };
}

export interface TreasuryKpis {
  daily_revenue: string;
  monthly_revenue: string;
  predial_collected: string;
  water_collected: string;
  fines_collected: string;
  pending_payments: number;
}

export interface RevenuePoint {
  label: string;
  date: string;
  amount: number;
}

export interface RevenueMonth {
  label: string;
  month: string;
  amount: number;
}

export interface ServiceDistribution {
  service_type: string;
  label: string;
  amount: number;
  percentage: number;
}

export interface PaymentBreakdown {
  key: string;
  label: string;
  amount: number;
  count: number;
  percentage: number;
}

export interface DashboardRow {
  icon: string;
  title: string;
  subtitle: string;
  amount: string;
  status?: string;
  reference?: string;
  paid_at?: string;
  issued_at?: string;
}

export interface TreasuryCaptureLineRequest {
  service_type: 'PREDIAL' | 'WATER' | 'TRAFFIC_FINE';
  lookup: string;
  include_oxxo_reference: boolean;
}

export interface TreasuryCaptureLineResponse {
  capture_line: {
    id: number;
    folio: string;
    service_type: string;
    service_id: number;
    amount: string;
    expiration_date: string;
    status: string;
  };
  citizen: {
    id: number;
    reference: string;
    name: string;
    address: string;
    service_type: string;
  };
  delivery: {
    payment_link: string;
    document_url: string;
    expires_at: string;
  };
  openpay: {
    gateway: string;
    method: string;
    reference: string;
    paynet_reference: string;
    store: string;
    amount: string;
    expires_at: string;
    instructions: string;
  } | null;
}

@Injectable({ providedIn: 'root' })
export class ReportsService {
  private readonly api = inject(ApiClientService);

  dashboard() {
    return this.api.get<TreasuryDashboard>('/treasury/dashboard');
  }

  createTreasuryCaptureLine(payload: TreasuryCaptureLineRequest) {
    return this.api.post<TreasuryCaptureLineResponse>('/treasury/capture-lines', payload);
  }
}
