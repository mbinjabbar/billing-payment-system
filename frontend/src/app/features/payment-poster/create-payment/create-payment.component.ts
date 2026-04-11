import { Component, inject } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FormGroup, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';



@Component({
  selector: 'app-create-payment',
  imports: [RouterLink, ReactiveFormsModule, CommonModule],
  templateUrl: './create-payment.component.html',
  styleUrl: './create-payment.component.css'
})
export class CreatePaymentComponent {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private paymentposterService = inject(PaymentPosterService);
  selectedfile: File | null = null;
  billId: number = 0;
  paymentId: number = 0;

  paymentForm = new FormGroup({
    amount_paid: new FormControl('', Validators.required),
    payment_mode: new FormControl('', Validators.required),
    check_number: new FormControl('', Validators.required),
    bank_name: new FormControl('', Validators.required),
    payment_date: new FormControl('', Validators.required),
    payment_status: new FormControl('', Validators.required),
    transaction_reference: new FormControl('', Validators.required),
    notes: new FormControl('')
  });


  ngOnInit() {
    const billId = this.route.snapshot.paramMap.get('billId');
    const editId = this.route.snapshot.paramMap.get('id');

    if (billId) {
      this.billId = parseInt(billId, 10);
      console.log(billId);
    }

    if (editId) {
      this.paymentId = parseInt(editId, 10);
        console.log('EDIT MODE ACTIVE');
  console.log('Payment ID:', this.paymentId);

      this.loadPayment(this.paymentId);
    }
  }
  loadPayment(id: number) {
    this.paymentposterService.getPaymentById(id).subscribe({
      next: (res: any) => {
        const data = res.data;
        if (data.payment_date) {
          data.payment_date = data.payment_date.split('T')[0];
        }
      this.billId = data.bill_id;
        this.paymentForm.patchValue(data);
      },
      error: (err) => console.log(err)
    });
  }
  onfileselected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
      this.selectedfile = input.files[0];
    }
  }


  onSubmit() {
    if (!this.paymentForm.valid) return;

    const paymentdata = new FormData();
    paymentdata.append('amount_paid', this.paymentForm.get('amount_paid')?.value ?? '');
    paymentdata.append('payment_mode', this.paymentForm.get('payment_mode')?.value ?? '');
    paymentdata.append('check_number', this.paymentForm.get('check_number')?.value ?? '');
    paymentdata.append('bank_name', this.paymentForm.get('bank_name')?.value ?? '');
    paymentdata.append('payment_date', this.paymentForm.get('payment_date')?.value ?? '');
    paymentdata.append('payment_status', this.paymentForm.get('payment_status')?.value ?? '');
    paymentdata.append('transaction_reference', this.paymentForm.get('transaction_reference')?.value ?? '');
    paymentdata.append('notes', this.paymentForm.get('notes')?.value ?? '');
    if (this.selectedfile) paymentdata.append('cheque_file', this.selectedfile);
    console.log("works")
    if (this.paymentId) {
        paymentdata.append('bill_id', this.billId.toString());
  paymentdata.append('_method', 'PUT');
      this.paymentposterService.updatePayment(this.paymentId, paymentdata).subscribe({
        next: () => {
          console.log('Updated');
          this.router.navigate(['/payment-list']);
        },
        error: (err) => console.log(err)
      });
    }

    else {
      paymentdata.append('bill_id', this.billId.toString());
      paymentdata.append('received_by', "1");
      this.paymentposterService.createPayment(paymentdata).subscribe({
        next: (response: any) => {
          console.log('Payment successful:', response.data);
          this.router.navigate(['/payment-list']);
        },
        error: (err) => {
          // 1. Log the entire error object for debugging
          console.error('Full error object:', err);

          // 2. Check the HTTP status code (e.g., 400, 404, 500)
          console.log('Status code:', err.status);

          // 3. Get the error message (client-side or server-side)
          console.log('Message:', err.message);

          // 4. Access the custom error body sent by your backend
          if (err.error) {
            console.log('Server-side error details:', err.error);
          }
        }
      })
    }
  }
}