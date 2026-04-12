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
    { path: 'bills/create/:visitId', loadComponent: () => import('./features/bills/create-bill/create-bill.component').then(m => m.CreateBillComponent)},
    { path: 'bills/invoice/:billId', loadComponent: () => import('./features/bills/bill-invoice/bill-invoice.component').then(m => m.BillInvoiceComponent)},
    { path: 'bills/bill-list', loadComponent: () => import('./features/bills/bill-list/bill-list.component').then(m => m.BillListComponent)},
    { path: 'bills/edit/:billId', loadComponent: () => import('./features/bills/edit-bill/edit-bill.component').then(m => m.EditBillComponent)},
    { path: 'documents', loadComponent: () => import('./features/documents/document-list/document-list.component').then(m => m.DocumentListComponent)},
    { path: 'payment-poster', loadComponent: () => import('./features/payment-poster/dashboard/dashboard.component').then(m => m.DashboardComponent) },
    {path: 'payments/payment-list',loadComponent: () =>import('./features/payments/payment-list/payment-list.component').then(m => m.PaymentListComponent)},
    { path: 'bills/:billId/pay', loadComponent: () => import('./features/payment-poster/create-payment/create-payment.component').then(m => m.CreatePaymentComponent)},
    { path: 'payments/edit/:id',  loadComponent: () => import('./features/payment-poster/create-payment/create-payment.component').then(m => m.CreatePaymentComponent)},
    { path: '**', redirectTo: 'login', pathMatch: 'full' }
];
