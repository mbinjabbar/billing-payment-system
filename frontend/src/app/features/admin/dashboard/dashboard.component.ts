import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { BillService } from '../../../core/services/bill.service';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { VisitService } from '../../../core/services/visit.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {
  private billService    = inject(BillService);
  private paymentService = inject(PaymentPosterService);
  private visitService   = inject(VisitService);

  bills    = signal<any[]>([]);
  payments = signal<any[]>([]);
  visitStats = signal<any>({ total_visits: 0, billed: 0, unbilled: 0 });
  recentBills    = signal<any[]>([]);
  recentPayments = signal<any[]>([]);

  loading = signal(true);

  ngOnInit() {
    let loaded = 0;
    const done = () => { if (++loaded === 3) this.loading.set(false); };

    // Bills — for billing stats + recent bills table
    this.billService.getBills({}).subscribe({
      next: (res: any) => {
        this.bills.set(res.data ?? []);
        this.recentBills.set((res.data ?? []).slice(0, 5));
        done();
      },
      error: () => done(),
    });

    // Payments — for payment stats + recent payments table
    this.paymentService.getPayments({ per_page: 5 }).subscribe({
      next: (res: any) => {
        this.payments.set(res.data ?? []);
        this.recentPayments.set(res.data ?? []);
        done();
      },
      error: () => done(),
    });

    // Visits — for visit stats from the stats key
    this.visitService.getVisits(1).subscribe({
      next: (res: any) => {
        this.visitStats.set(res.stats ?? { total_visits: 0, billed: 0, unbilled: 0 });
        done();
      },
      error: () => done(),
    });
  }

  // ── Bill stats ────────────────────────────────────────────────────────────
  totalBilled = computed(() =>
    this.bills().reduce((sum, b) => sum + Number(b.bill_amount), 0)
  );

  totalOutstanding = computed(() =>
    this.bills().reduce((sum, b) => sum + Number(b.outstanding_amount), 0)
  );

  totalCollected = computed(() =>
    this.bills().reduce((sum, b) => sum + Number(b.paid_amount), 0)
  );

  paidBillsCount = computed(() =>
    this.bills().filter(b => b.status === 'Paid').length
  );

  pendingBillsCount = computed(() =>
    this.bills().filter(b => b.status === 'Pending').length
  );

  partialBillsCount = computed(() =>
    this.bills().filter(b => b.status === 'Partial').length
  );

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
      case 'Paid':        return 'bg-green-100 text-green-700';
      case 'Pending':     return 'bg-orange-100 text-orange-700';
      case 'Partial':     return 'bg-blue-100 text-blue-700';
      case 'Cancelled':   return 'bg-gray-200 text-gray-600';
      case 'Draft':       return 'bg-purple-100 text-purple-700';
      case 'Written Off': return 'bg-red-100 text-red-700';
      default:            return 'bg-gray-100 text-gray-700';
    }
  }

  getPaymentStatusClass(status: string): string {
    switch (status) {
      case 'Completed': return 'bg-green-100 text-green-700';
      case 'Pending':   return 'bg-orange-100 text-orange-700';
      case 'Failed':    return 'bg-red-100 text-red-700';
      case 'Refunded':  return 'bg-gray-200 text-gray-600';
      default:          return 'bg-gray-100 text-gray-700';
    }
  }
}