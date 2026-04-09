import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';

interface NavItem {
  label: string;
  icon: string;
  route: string;
}

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './sidebar.component.html',
})
export class SidebarComponent {
  navItems: NavItem[] = [
    { label: 'Dashboard', icon: 'dashboard', route: '/biller' },
    { label: 'Visits', icon: 'calendar_today', route: '/biller/visits' },
    { label: 'Bills', icon: 'receipt_long', route: 'bills/bill-list' },
    { label: 'Documents', icon: 'description', route: '/documents' },
  ];
}