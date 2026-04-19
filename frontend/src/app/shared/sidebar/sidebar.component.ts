import { Component, computed, inject } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../core/services/auth.service';

interface NavItem {
  label: string;
  icon:  string;
  route: string;
}

const BILLER_NAV: NavItem[] = [
  { label: 'Dashboard', icon: 'dashboard',      route: '/biller' },
  { label: 'Patients',  icon: 'person',          route: '/patients' },
  { label: 'Visits',    icon: 'calendar_today', route: '/visits' },
  { label: 'Bills',     icon: 'receipt_long',   route: '/bills' },
  { label: 'Documents', icon: 'description',    route: '/documents' },
];

const PAYMENT_POSTER_NAV: NavItem[] = [
  { label: 'Dashboard', icon: 'dashboard',      route: '/payment-poster' },
  { label: 'Bills',     icon: 'receipt_long',   route: '/bills' },
  { label: 'Payments',  icon: 'payments',       route: '/payments' },
  { label: 'Documents', icon: 'description',    route: '/documents' },
];

const ADMIN_NAV: NavItem[] = [
  { label: 'Dashboard', icon: 'dashboard',      route: '/admin' },
  { label: 'Patients',  icon: 'person',          route: '/patients' },
  { label: 'Visits',    icon: 'calendar_today', route: '/visits' },
  { label: 'Bills',     icon: 'receipt_long',   route: '/bills' },
  { label: 'Payments',  icon: 'payments',       route: '/payments' },
  { label: 'Documents', icon: 'description',    route: '/documents' },
  { label: 'Users',     icon: 'group',           route: '/admin/users' },
  { label: 'Settings',  icon: 'settings',        route: '/admin/settings' },
];

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, CommonModule],
  templateUrl: './sidebar.component.html',
})
export class SidebarComponent {
  private authService = inject(AuthService);

  user = computed(() => this.authService.getUser());

  navItems = computed((): NavItem[] => {
    switch (this.user()?.role) {
      case 'Admin':          return ADMIN_NAV;
      case 'Payment Poster': return PAYMENT_POSTER_NAV;
      case 'Biller':
      default:               return BILLER_NAV;
    }
  });
}