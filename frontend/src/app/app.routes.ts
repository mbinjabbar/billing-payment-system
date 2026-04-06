import { Routes } from '@angular/router';

export const routes: Routes = [
    { path: '', redirectTo: 'login', pathMatch: 'full' },
    { path: 'login', loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent) },
    { path: 'admin', loadComponent: () => import('./features/admin/dashboard/dashboard.component').then(m => m.DashboardComponent) },
    { path: 'biller', loadComponent: () => import('./features/biller/dashboard/dashboard.component').then(m => m.DashboardComponent) },
    { path: 'payment-poster', loadComponent: () => import('./features/payment-poster/dashboard/dashboard.component').then(m => m.DashboardComponent) },
];
