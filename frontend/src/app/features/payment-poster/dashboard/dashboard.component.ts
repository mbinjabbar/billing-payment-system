import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {
  private paymentService = inject(PaymentPosterService);
  private billService    = inject(BillService);

  payments = signal<any[]>([]);
  bills    = signal<any[]>([]);
  loading  = signal(true);

  ngOnInit() {
    // Load recent payments (latest 5 for the table)
    this.paymentService.getPayments({ per_page: 5 }).subscribe({
      next: (res: any) => {
        this.payments.set(res.data ?? []);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });

    // Load bills to calculate A/R stats
    this.billService.getBills({}).subscribe({
      next: (res: any) => this.bills.set(res.data ?? []),
      error: () => {},
    });
  }

  // ── Stats ────────────────────────────────────────────────────────────────
  totalCollected = computed(() =>
    this.payments().reduce((sum, p) => sum + Number(p.amount_paid), 0)
  );

  totalOutstanding = computed(() =>
    this.bills().reduce((sum, b) => sum + Number(b.outstanding_amount), 0)
  );

  totalBillAmount = computed(() =>
    this.bills().reduce((sum, b) => sum + Number(b.bill_amount), 0)
  );

  pendingBillsCount = computed(() =>
    this.bills().filter(b => b.status === 'Pending').length
  );

  collectionProgress = computed(() => {
    const total = this.totalBillAmount();
    if (!total) return 0;
    const pct = (this.totalCollected() / total) * 100;
    return pct > 100 ? 100 : pct;
  });

  // ── UI helpers ───────────────────────────────────────────────────────────
  getPaymentStatusClass(status: string): string {
    switch (status) {
      case 'Completed': return 'bg-green-100 text-green-700';
      case 'Pending':   return 'bg-orange-100 text-orange-700';
      case 'Failed':    return 'bg-red-100 text-red-700';
      case 'Refunded':  return 'bg-gray-200 text-gray-600';
      default:          return 'bg-gray-100 text-gray-700';
    }
  }

  getPaymentModeClass(mode: string): string {
    switch (mode) {
      case 'Cash':           return 'bg-green-100 text-green-700';
      case 'Check':          return 'bg-blue-100 text-blue-700';
      case 'Bank Transfer':  return 'bg-indigo-100 text-indigo-700';
      case 'Credit Card':    return 'bg-purple-100 text-purple-700';
      case 'Debit Card':     return 'bg-violet-100 text-violet-700';
      case 'Insurance':      return 'bg-cyan-100 text-cyan-700';
      case 'Online Payment': return 'bg-orange-100 text-orange-700';
      default:               return 'bg-gray-100 text-gray-700';
    }
  }
}