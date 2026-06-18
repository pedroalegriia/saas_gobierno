export type ServiceType = 'PREDIAL' | 'WATER' | 'TRAFFIC_FINE';

export interface DebtSummary {
  id: number;
  status: string;
  current_balance?: string;
  overdue_balance?: string;
  total_balance?: string;
  amount?: string;
}

export interface CaptureLine {
  id: number;
  folio: string;
  service_type: ServiceType;
  service_id: number;
  amount: string;
  expiration_date: string;
  status: 'PENDING' | 'PAID' | 'EXPIRED' | 'CANCELLED';
}

export interface Payment {
  id: number;
  capture_line_id: number;
  gateway: string;
  method: string;
  amount: string;
  reference: string | null;
  status: string;
}
