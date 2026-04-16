import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { BillService } from '../../../core/services/bill.service';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {
  private billService = inject(BillService);
  private paymentService = inject(PaymentPosterService);

  bills = signal<any[]>([]);
  payments = signal<any[]>([]);
  recentBills = signal<any[]>([]);
  recentPayments = signal<any[]>([]);

  stats = signal<any>({
    total_bill_amount: 0,
    total_paid_amount: 0,
    total_outstanding: 0,
    pending_count: 0,
    partial_count: 0,
    paid_count: 0,
  });

  loading = signal(true);

  ngOnInit() {
    let loaded = 0;
    const done = () => {
      if (++loaded === 2) this.loading.set(false);
    };

    // Bills — for billing stats + recent bills table
    this.billService.getBills({ limit: 5 }).subscribe({
      next: (res: any) => {
        this.recentBills.set((res.data ?? []).slice(0, 5));
        if (res.stats) this.stats.set(res.stats);
        done();
      },
      error: () => done(),
    });

    // Payments — for payment stats + recent payments table
    this.paymentService.getPayments({ limit: 5 }).subscribe({
      next: (res: any) => {
        this.payments.set(res.data ?? []);
        this.recentPayments.set(res.data ?? []);
        done();
      },
      error: () => done(),
    });
  }

  // ── Bill stats ────────────────────────────────────────────────────────────
  totalBilled = computed(() => Number(this.stats().total_bill_amount));
  totalCollected = computed(() => Number(this.stats().total_paid_amount));
  totalOutstanding = computed(() => Number(this.stats().total_outstanding));
  pendingBillsCount = computed(() => this.stats().pending_count);
  partialBillsCount = computed(() => this.stats().partial_count);
  paidBillsCount = computed(() => this.stats().paid_count);

  collectionProgress = computed(() => {
    const total = this.totalBilled();
    if (!total) return 0;
    const pct = (this.totalCollected() / total) * 100;
    return pct > 100 ? 100 : pct;
  });

  // ── Payment stats ────────────────────────────────────────────────────────
  totalPaymentsCount = computed(() => this.payments().length);

  // ── UI helpers ───────────────────────────────────────────────────────────
  getBillStatusClass(status: string): string {
    switch (status) {
      case 'Paid':
        return 'bg-green-100 text-green-700';
      case 'Pending':
        return 'bg-orange-100 text-orange-700';
      case 'Partial':
        return 'bg-blue-100 text-blue-700';
      case 'Cancelled':
        return 'bg-gray-200 text-gray-600';
      case 'Draft':
        return 'bg-purple-100 text-purple-700';
      case 'Written Off':
        return 'bg-red-100 text-red-700';
      default:
        return 'bg-gray-100 text-gray-700';
    }
  }

  getPaymentStatusClass(status: string): string {
    switch (status) {
      case 'Completed':
        return 'bg-green-100 text-green-700';
      case 'Pending':
        return 'bg-orange-100 text-orange-700';
      case 'Failed':
        return 'bg-red-100 text-red-700';
      case 'Refunded':
        return 'bg-gray-200 text-gray-600';
      default:
        return 'bg-gray-100 text-gray-700';
    }
  }
}
