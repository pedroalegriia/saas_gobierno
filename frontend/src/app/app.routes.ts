import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  {
    path: '',
    loadComponent: () => import('./features/citizen/citizen-home.page').then((m) => m.CitizenHomePage),
  },
  {
    path: 'consulta/:service',
    loadComponent: () => import('./features/citizen/debt-search.page').then((m) => m.DebtSearchPage),
  },
  {
    path: 'pagar/:folio',
    loadComponent: () => import('./features/citizen/payment.page').then((m) => m.PaymentPage),
  },
  {
    path: 'recibos',
    loadComponent: () => import('./features/citizen/receipts.page').then((m) => m.ReceiptsPage),
  },
  {
    path: 'login',
    loadComponent: () => import('./features/auth/login.page').then((m) => m.LoginPage),
  },
  {
    path: 'tesoreria',
    canActivate: [authGuard],
    loadComponent: () => import('./features/treasury/treasury-dashboard.page').then((m) => m.TreasuryDashboardPage),
  },
  {
    path: '**',
    redirectTo: '',
  },
];
