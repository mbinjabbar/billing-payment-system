import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

interface BillingEntry {
  billId: string;
  time: string;
  initials: string;
  initialsColor: string;
  patientName: string;
  status: 'DRAFT' | 'SUBMITTED' | 'FLAGGED';
  amount: string;
}

interface Task {
  icon: string;
  iconColor: string;
  title: string;
  subtitle: string;
}

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {

  billingEntries: BillingEntry[] = [
    {
      billId: '#LL-89021', time: 'Today, 09:42 AM',
      initials: 'EJ', initialsColor: 'bg-primary-container text-primary',
      patientName: 'Elena Jenkins', status: 'DRAFT', amount: '$1,240.00',
    },
    {
      billId: '#LL-89019', time: 'Today, 08:15 AM',
      initials: 'MR', initialsColor: 'bg-tertiary-container text-on-tertiary-container',
      patientName: 'Marcus Rivera', status: 'SUBMITTED', amount: '$840.50',
    },
    {
      billId: '#LL-89015', time: 'Yesterday, 04:30 PM',
      initials: 'SM', initialsColor: 'bg-error-container/20 text-error',
      patientName: 'Sarah Miller', status: 'FLAGGED', amount: '$3,100.00',
    },
    {
      billId: '#LL-89010', time: 'Yesterday, 02:12 PM',
      initials: 'TC', initialsColor: 'bg-surface-container-high text-on-surface-variant',
      patientName: 'Thomas Chen', status: 'SUBMITTED', amount: '$155.00',
    },
  ];

  getStatusClass(status: string): string {
    switch (status) {
      case 'DRAFT':     return 'bg-primary-container text-on-primary-container';
      case 'SUBMITTED': return 'bg-secondary-container text-on-secondary-container';
      case 'FLAGGED':   return 'bg-error-container/20 text-error';
      default:          return 'bg-surface-container-high text-on-surface-variant';
    }
  }
}