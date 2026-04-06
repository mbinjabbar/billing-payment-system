import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent {
  private billService = inject(BillService);
  bills: any = [];
  totalBill = 0;

  ngOnInit(){
    this.billService.getBills().subscribe(data => this.bills = data)
  }

  totalBillAmount(){
    for(let bill of this.bills.data){
      this.totalBill += Number(bill.bill_amount);
    }
    return this.totalBill;
  }

  getStatusClass(status: string): string {
    switch (status) {
      case 'Draft':     return 'bg-primary-container text-on-primary-container';
      case 'Submitted': return 'bg-secondary-container text-on-secondary-container';
      case 'Pending':   return 'bg-error-container/20 text-error';
      default:          return 'bg-surface-container-high text-on-surface-variant';
    }
  }
}