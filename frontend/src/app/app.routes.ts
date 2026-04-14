import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { roleGuard } from './core/guards/role.guard';
import { loginGuard } from './core/guards/login.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  {
    path: 'login',
    canActivate: [loginGuard],
    loadComponent: () =>
      import('./features/auth/login/login.component').then(m => m.LoginComponent),
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
      {
        path: 'settings',
        loadComponent: () =>
          import('./features/admin/settings/settings.component').then(m => m.SettingsComponent)
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
      }
    ]
  },

  // Visits
  {
    path: 'visits',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller'])],
    loadComponent: () =>
      import('./features/bills/visit/visit.component').then(m => m.VisitComponent)
  },

  // Bills — specific routes
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
  {
    // bill list
    path: 'bills',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/bills/bill-list/bill-list.component').then(m => m.BillListComponent)
  },

  // Payments
  {
    path: 'payments/edit/:id',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/payment-form/payment-form.component').then(m => m.PaymentFormComponent)
  },
  {
    // payment list
    path: 'payments',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payments/payment-list/payment-list.component').then(m => m.PaymentListComponent)
  },

  // Payment Poster
  {
    path: 'payment-poster',
    canActivate: [authGuard, roleGuard(['Admin', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/payment-poster/dashboard/dashboard.component').then(m => m.DashboardComponent)
  },

  // Documents
  {
    path: 'documents',
    canActivate: [authGuard, roleGuard(['Admin', 'Biller', 'Payment Poster'])],
    loadComponent: () =>
      import('./features/documents/document-list/document-list.component').then(m => m.DocumentListComponent)
  },
  // 404 Not Found
  {
    path: 'not-found',
    loadComponent: () =>
      import('./shared/not-found/not-found.component').then(m=> m.NotFoundComponent)
  },
    // 403 Forbidden
  {
    path: 'forbidden',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./shared/forbidden/forbidden.component').then(m=> m.ForbiddenComponent)
  },
  { path: '**', redirectTo: 'not-found', pathMatch: 'full' }
];