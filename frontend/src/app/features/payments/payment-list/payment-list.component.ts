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
  const filters = this.filterForm.value;
  this.paymentposterService.getPayments(filters).subscribe({
    next: (res: any) => {
      this.paymentsList = res.data.data || res.data;
       this.uniqueBills = [
        ...new Set(this.paymentsList.map(p => p.bill_id))
      ];
  
    },
    error: (err) => {
      console.error('Error fetching payments', err);
    }
  });
}
resetFilters() {
  
 this.filterForm.reset({
   bill_id: '',
    payment_mode: '',
    payment_status: '',
    from_date: '',
    to_date: ''
  });
  this.paymentposterService.getPayments({}).subscribe({
    next: (res: any) => {
      this.paymentsList = res.data.data || res.data;
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
