import { Component,inject } from '@angular/core';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { DatePipe } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl } from '@angular/forms';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-payment-list',
  imports: [DatePipe,RouterLink, ReactiveFormsModule],
  templateUrl: './payment-list.component.html',
  styleUrl: './payment-list.component.css'
})
export class PaymentListComponent {
  private paymentposterService = inject(PaymentPosterService);
  paymentsList: any[] = []; 
  uniqueBills: number[] = [];

  filterForm = new FormGroup({
  bill_id: new FormControl(''), 
  payment_mode: new FormControl(''),
  payment_status: new FormControl(''),
  from_date: new FormControl(''),
  to_date: new FormControl('')
});

  ngOnInit(): void {
    this.getPayments();
  }

  
  getPayments() {
    const filters = this.getFilters();
    this.paymentposterService.getPayments(filters).subscribe({
      next: (res: any) => {
        this.paymentsList = res.data.data || res.data;
        this.uniqueBills = [
          ...new Set(this.paymentsList.map(p => p.bill_id))
        ];
      },
      error: (err) => {
        console.log(err);
      }
    });
  }

  private cleanFilters(filters: any): any {
    const cleaned: any = {};
    Object.keys(filters).forEach(key => {
      const value = filters[key];
      if (value !== null && value !== '' && value !== undefined) {
        cleaned[key] = value;
      }
    });
    return cleaned;
  }

  private getFilters(): any {
    return this.cleanFilters(this.filterForm.value);
  }

  resetFilters() {
    this.filterForm.reset();
    this.getPayments();
  }

  exportPayments() {
  const filters = this.getFilters();
  this.paymentposterService.exportPayments(filters).subscribe({
    next: (res: any) => {
      const blob = new Blob([res], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'payments.xlsx';
      a.click();
    },
    error: (err) => {
      console.log(err);
    }
  });
}

  deletePayment(id: number) {
   this.paymentposterService.deletePayment(id).subscribe({
    next: () => {
      this.getPayments(); 
    },
    error: (err) => {
      console.log(err);
    }
  });
}

}
