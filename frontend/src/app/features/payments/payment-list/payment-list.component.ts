import { Component,inject } from '@angular/core';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-payment-list',
  imports: [DatePipe,RouterLink],
  templateUrl: './payment-list.component.html',
  styleUrl: './payment-list.component.css'
})
export class PaymentListComponent {
  private paymentposterService = inject(PaymentPosterService);

  paymentsList: any[] = []; 

  ngOnInit(): void {
    this.getPayments();
  }

  getPayments() {
    this.paymentposterService.getPayments().subscribe({
      next: (res: any) => {
        console.log(res);
        this.paymentsList = res.data;
      },
      error: (err) => {
        console.error('Error fetching payments', err);
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
