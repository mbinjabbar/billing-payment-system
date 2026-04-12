import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
  // Public
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  {
    path: 'login',
    loadComponent: () =>
      import('./features/auth/login/login.component').then(m => m.LoginComponent)
  },

  // Admin
  {
    path: 'admin',
    canActivate: [authGuard, roleGuard(['Admin'])],
    loadComponent: () =>
      import('./features/admin/dashboard/dashboard.component').then(m => m.DashboardComponent)
  },

  // Biller
  {
    path: 'biller',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller'])],
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./features/biller/dashboard/dashboard.component').then(m => m.DashboardComponent)
      },
      {
        path: 'visits',
        loadComponent: () =>
          import('./features/biller/visit/visit.component').then(m => m.VisitComponent)
      }
    ]
  },

  // Bills — Biller + Admin can create/edit, all roles can view list/invoice
  {
    path: 'bills/create/:visitId',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller'])],
    loadComponent: () =>
      import('./features/bills/create-bill/create-bill.component').then(m => m.CreateBillComponent)
  },
  {
    path: 'bills/edit/:billId',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller'])],
    loadComponent: () =>
      import('./features/bills/edit-bill/edit-bill.component').then(m => m.EditBillComponent)
  },
  {
    path: 'bills/bill-list',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/bills/bill-list/bill-list.component').then(m => m.BillListComponent)
  },
  {
    path: 'bills/invoice/:billId',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/bills/bill-invoice/bill-invoice.component').then(m => m.BillInvoiceComponent)
  },

  // Payments — Payment Poster + Admin
  {
    path: 'payment-poster',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payment-poster/dashboard/dashboard.component').then(m => m.DashboardComponent)
  },
  {
    path: 'bills/:billId/pay',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/create-payment/create-payment.component').then(m => m.CreatePaymentComponent)
  },
  {
    path: 'payments/edit/:id',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/create-payment/create-payment.component').then(m => m.CreatePaymentComponent)
  },
  {
    path: 'payments/payment-list',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/payment-list/payment-list.component').then(m => m.PaymentListComponent)
  },

  // Documents — all roles
  {
    path: 'documents',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/documents/document-list/document-list.component').then(m => m.DocumentListComponent)
  },

  // Fallback
  { path: '**', redirectTo: 'login', pathMatch: 'full' }
];