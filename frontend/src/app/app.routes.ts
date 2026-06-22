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
    path: 'historial',
    loadComponent: () => import('./features/citizen/citizen-history.page').then((m) => m.CitizenHistoryPage),
  },
  {
    path: 'login',
    loadComponent: () => import('./features/auth/login.page').then((m) => m.LoginPage),
  },
  {
    path: 'tesoreria',
    canActivate: [authGuard],
    loadComponent: () => import('./features/treasury/treasury-shell.page').then((m) => m.TreasuryShellPage),
    children: [
      {
        path: '',
        pathMatch: 'full',
        redirectTo: 'resumen',
      },
      {
        path: 'resumen',
        loadComponent: () => import('./features/treasury/treasury-dashboard.page').then((m) => m.TreasuryDashboardPage),
      },
      {
        path: 'lineas-captura',
        loadComponent: () => import('./features/treasury/treasury-capture-lines.page').then((m) => m.TreasuryCaptureLinesPage),
      },
      {
        path: 'pagos',
        loadComponent: () => import('./features/treasury/treasury-payments.page').then((m) => m.TreasuryPaymentsPage),
      },
      {
        path: 'adeudos',
        loadComponent: () => import('./features/treasury/treasury-debts.page').then((m) => m.TreasuryDebtsPage),
      },
      {
        path: 'recibos',
        loadComponent: () => import('./features/treasury/treasury-receipts.page').then((m) => m.TreasuryReceiptsPage),
      },
      {
        path: 'reportes',
        loadComponent: () => import('./features/treasury/treasury-reports.page').then((m) => m.TreasuryReportsPage),
      },
    ],
  },
  {
    path: 'admin',
    canActivate: [authGuard],
    loadComponent: () => import('./features/admin/admin-shell.page').then((m) => m.AdminShellPage),
    children: [
      { path: '', pathMatch: 'full', redirectTo: 'metricas' },
      { path: 'metricas', loadComponent: () => import('./features/admin/admin-metrics.page').then((m) => m.AdminMetricsPage) },
      { path: 'municipios', loadComponent: () => import('./features/admin/admin-municipalities.page').then((m) => m.AdminMunicipalitiesPage) },
      { path: 'usuarios', loadComponent: () => import('./features/admin/admin-users.page').then((m) => m.AdminUsersPage) },
    ],
  },
  {
    path: '**',
    redirectTo: '',
  },
];
