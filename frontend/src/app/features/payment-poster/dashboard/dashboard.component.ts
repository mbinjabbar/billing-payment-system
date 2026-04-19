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

  payments  = signal<any[]>([]);
  billStats = signal<any>({
    total_bill_amount: 0,
    total_paid_amount: 0,
    total_outstanding: 0,
    pending_count:     0,
  });
  loading = signal(true);

  ngOnInit() {
    let loaded = 0;
    const done = () => { if (++loaded === 2) this.loading.set(false); };

    // Recent payments for the table only
    this.paymentService.getPayments({ limit: 5 }).subscribe({
      next: (res: any) => { this.payments.set(res.data ?? []); done(); },
      error: () => done(),
    });

    // Bills — stats from res.stats (full dataset aggregates)
    this.billService.getBills({}).subscribe({
      next: (res: any) => {
        if (res.stats) this.billStats.set(res.stats);
        done();
      },
      error: () => done(),
    });
  }

  // Stats
  totalCollected = computed(() =>
    Number(this.billStats().total_paid_amount)
  );

  totalOutstanding = computed(() =>
    Number(this.billStats().total_outstanding)
  );

  totalBillAmount = computed(() =>
    Number(this.billStats().total_bill_amount)
  );

  pendingBillsCount = computed(() =>
    this.billStats().pending_count
  );

  collectionProgress = computed(() => {
    const total = this.totalBillAmount();
    if (!total) return 0;
    const pct = (this.totalCollected() / total) * 100;
    return pct > 100 ? 100 : pct;
  });

  // UI helpers
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