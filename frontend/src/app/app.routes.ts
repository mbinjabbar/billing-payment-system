import { Routes } from '@angular/router';

export const routes: Routes = [
    { path: '', redirectTo: 'login', pathMatch: 'full' },
    { path: 'login', loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent) },
    { path: 'admin', loadComponent: () => import('./features/admin/dashboard/dashboard.component').then(m => m.DashboardComponent) },
  {
    path: 'biller',
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./features/biller/dashboard/dashboard.component')
            .then(m => m.DashboardComponent)
      },
      {
        path: 'visits',
        loadComponent: () =>
          import('./features/biller/visit/visit.component')
            .then(m => m.VisitComponent)
      }
    ]
  },
    { path: 'payment-poster', loadComponent: () => import('./features/payment-poster/dashboard/dashboard.component').then(m => m.DashboardComponent) },
    { path: '**', redirectTo: 'login', pathMatch: 'full' }
];
