import { Injectable, inject } from '@angular/core';
import { CaptureLine, DebtSummary, Payment, ServiceType } from '../models/payment.model';
import { ApiClientService } from './api-client.service';

@Injectable({ providedIn: 'root' })
export class PaymentsService {
  private readonly api = inject(ApiClientService);

  searchPredial(propertyKey: string) {
    return this.api.get<{ data: DebtSummary }>('/predial/accounts/search', { property_key: propertyKey });
  }

  searchWater(contractNumber: string) {
    return this.api.get<{ data: DebtSummary }>('/water/accounts/search', { contract_number: contractNumber });
  }

  searchTrafficFines(query: { folio?: string; plate?: string }) {
    return this.api.get<{ data: DebtSummary[] }>('/traffic-fines/search', query as Record<string, string>);
  }

  createCaptureLine(serviceType: ServiceType, serviceId: number) {
    return this.api.post<{ data: CaptureLine }>('/capture-lines', {
      service_type: serviceType,
      service_id: serviceId,
    });
  }

  pay(captureLineFolio: string, gateway: string, method: string, paymentToken: string) {
    return this.api.post<{ data: Payment }>('/payments', {
      capture_line_folio: captureLineFolio,
      gateway,
      method,
      payment_token: paymentToken,
    });
  }
}
