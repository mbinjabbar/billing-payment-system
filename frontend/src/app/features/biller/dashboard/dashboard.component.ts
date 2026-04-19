import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {
  private billService = inject(BillService);

  bills = signal<any>({ data: [] });
  stats = signal<any>({
    total_bill_amount: 0,
    total_paid_amount: 0,
    total_outstanding: 0,
    pending_count: 0,
    partial_count: 0,
    paid_count: 0,
  });

  ngOnInit() {
    this.billService.getBills({ limit: 5 }).subscribe((res: any) => {
      this.bills.set(res);
      if (res.stats) this.stats.set(res.stats);
    });
  }

  // From stats
  totalBillAmount = computed(() => Number(this.stats().total_bill_amount));
  totalOutstandingAmount = computed(() => Number(this.stats().total_outstanding));
  totalPaidAmount = computed(() => Number(this.stats().total_paid_amount));
  pendingBillsCount = computed(() => this.stats().pending_count);

  collectionProgress = computed(() => {
    const total = this.totalBillAmount();
    if (!total) return 0;
    const pct = (this.totalPaidAmount() / total) * 100;
    return pct > 100 ? 100 : pct;
  });

  getStatusClass(status: string): string {
    switch (status) {
      case 'Paid':        return 'bg-green-100 text-green-700';
      case 'Pending':     return 'bg-orange-100 text-orange-700';
      case 'Partial':     return 'bg-blue-100 text-blue-700';
      case 'Draft':       return 'bg-purple-100 text-purple-700';
      case 'Cancelled':   return 'bg-gray-200 text-gray-600';
      case 'Written Off': return 'bg-red-100 text-red-700';
      default:            return 'bg-surface-container-high text-on-surface-variant';
    }
  }
}