import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent {
payments = [
  {
    id: 'PMT-001',
    patient: 'Ali Khan',
    status: 'Posted',
    amount: 5000,
    date: '2026-04-08'
  },
  {
    id: 'PMT-002',
    patient: 'Sara Ahmed',
    status: 'Unapplied',
    amount: 3200,
    date: '2026-04-07'
  },
  {
    id: 'PMT-003',
    patient: 'Usman Tariq',
    status: 'Posted',
    amount: 8700,
    date: '2026-04-06'
  }
];

bills = [
  { bill_amount: 20000, outstanding_amount: 5000 },
  { bill_amount: 15000, outstanding_amount: 3000 }
];

// Stats
totalCollected() {
  return this.payments.reduce((sum, p) => sum + p.amount, 0);
}

remainingAR() {
  return this.bills.reduce((sum, b) => sum + b.outstanding_amount, 0);
}

unappliedPaymentsCount() {
  return this.payments.filter(p => p.status === 'Unapplied').length;
}

collectionProgress() {
  const totalBills = this.bills.reduce((sum, b) => sum + b.bill_amount, 0);
  const collected = this.totalCollected();
  return totalBills ? (collected / totalBills) * 100 : 0;
}

// Status UI
getPaymentStatusClass(status: string) {
  switch (status) {
    case 'Posted':
      return 'bg-green-100 text-green-800';
    case 'Unapplied':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}
}
