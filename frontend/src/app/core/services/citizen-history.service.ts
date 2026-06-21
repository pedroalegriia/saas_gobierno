import { Injectable, signal } from '@angular/core';

export interface CitizenHistoryItem {
  folio: string;
  serviceType?: string;
  amount?: string;
  status: string;
  paymentReference?: string | null;
  createdAt: string;
}

const STORAGE_KEY = 'municipal_saas_citizen_history';

@Injectable({ providedIn: 'root' })
export class CitizenHistoryService {
  private readonly itemsSignal = signal<CitizenHistoryItem[]>(this.read());
  readonly items = this.itemsSignal.asReadonly();

  add(item: CitizenHistoryItem): void {
    const next = [
      item,
      ...this.itemsSignal().filter((existing) => existing.folio !== item.folio),
    ].slice(0, 20);

    localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    this.itemsSignal.set(next);
  }

  search(query: string): CitizenHistoryItem[] {
    const normalized = query.trim().toLowerCase();
    if (!normalized) {
      return this.itemsSignal();
    }

    return this.itemsSignal().filter((item) =>
      item.folio.toLowerCase().includes(normalized)
      || (item.paymentReference ?? '').toLowerCase().includes(normalized)
      || (item.serviceType ?? '').toLowerCase().includes(normalized),
    );
  }

  clear(): void {
    localStorage.removeItem(STORAGE_KEY);
    this.itemsSignal.set([]);
  }

  private read(): CitizenHistoryItem[] {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]') as CitizenHistoryItem[];
    } catch {
      return [];
    }
  }
}
