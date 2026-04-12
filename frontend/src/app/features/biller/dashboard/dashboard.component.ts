import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {
  private billService = inject(BillService);

  bills = signal<any>({ data: [] });

  billingGoal = 1000;

  ngOnInit() {
    this.billService.getBills({}).subscribe((data) => {
      this.bills.set(data);
    });
  }

  totalBillAmount = computed(() =>
    this.bills().data.reduce((sum: number, bill: any) =>
      sum + Number(bill.bill_amount), 0)
  );

  totalOutstandingAmount = computed(() =>
    this.bills().data.reduce((sum: number, bill: any) =>
      sum + Number(bill.outstanding_amount), 0)
  );

  pendingBillsCount = computed(() =>
    this.bills().data.filter((bill: any) =>
      bill.status === 'Pending').length
  );

  totalBillProgress = computed(() => {
    const progress = (this.totalBillAmount() / this.billingGoal) * 100;
    return progress > 100 ? 100 : progress;
  });

  getStatusClass(status: string): string {
    switch (status) {
      case 'Draft':     return 'bg-primary-container text-on-primary-container';
      case 'Submitted': return 'bg-secondary-container text-on-secondary-container';
      case 'Pending':   return 'bg-error-container/20 text-error';
      default:          return 'bg-surface-container-high text-on-surface-variant';
    }
  }
}