import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
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
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./features/admin/dashboard/dashboard.component').then(m => m.DashboardComponent)
      },
      {
        path: 'users',
        loadComponent: () =>
          import('./features/admin/users/user-list/user-list.component').then(m => m.UserListComponent)
      },
      {
        path: 'users/create',
        loadComponent: () =>
          import('./features/admin/users/user-form/user-form.component').then(m => m.UserFormComponent)
      },
      {
        path: 'users/edit/:id',
        loadComponent: () =>
          import('./features/admin/users/user-form/user-form.component').then(m => m.UserFormComponent)
      },
    ]
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
          import('./features/bills/visit/visit.component').then(m => m.VisitComponent)
      }
    ]
  },

  // Bills
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
  {
    path: 'bills/:billId/pay',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/payment-form/payment-form.component').then(m => m.PaymentFormComponent)
  },

  // Payments
  {
    path: 'payment-poster',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payment-poster/dashboard/dashboard.component').then(m => m.DashboardComponent)
  },
  {
    path: 'payments/payment-list',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/payment-list/payment-list.component').then(m => m.PaymentListComponent)
  },
  {
    path: 'payments/edit/:id',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/payment-form/payment-form.component').then(m => m.PaymentFormComponent)
  },

  // Documents
  {
    path: 'documents',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/documents/document-list/document-list.component').then(m => m.DocumentListComponent)
  },

  { path: '**', redirectTo: 'login', pathMatch: 'full' }
];