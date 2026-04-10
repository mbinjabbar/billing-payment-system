import { Component ,inject} from '@angular/core';
import { ActivatedRoute, Router,RouterLink } from '@angular/router';
import { FormGroup, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';



@Component({
  selector: 'app-create-payment',
  imports: [RouterLink,ReactiveFormsModule,CommonModule],
  templateUrl: './create-payment.component.html',
  styleUrl: './create-payment.component.css'
})
export class CreatePaymentComponent {
  private route                = inject(ActivatedRoute);
  private router                = inject(Router);
  private paymentposterService = inject(PaymentPosterService);
  selectedfile: File | null = null;
  billId: number = 0;

   paymentForm = new FormGroup({
    amount_paid: new FormControl('', Validators.required),
    payment_mode: new FormControl('', Validators.required),
    check_number:new FormControl('',Validators.required),
    bank_name:new FormControl('',Validators.required),
    payment_date:new FormControl('',Validators.required),
    payment_status: new FormControl('', Validators.required),
    transaction_reference:new FormControl('',Validators.required),
    notes:new FormControl('')
  });


  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('billId');
    this.billId = id ? parseInt(id, 10) : 0;
  }

   onfileselected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
      this.selectedfile = input.files[0];
    }
  }


   onSubmit() {
    // if (!this.paymentForm.valid) return;
    //  if (!this.selectedfile) {
    //    alert("Please upload a chequee file");
    //    return;
    // }

    const paymentdata = new FormData();
    paymentdata.append('amount_paid', this.paymentForm.get('amount_paid')?.value ?? '');
    paymentdata.append('payment_mode', this.paymentForm.get('payment_mode')?.value ?? '');
    paymentdata.append('check_number', this.paymentForm.get('check_number')?.value ?? '');
    paymentdata.append('bank_name', this.paymentForm.get('bank_name')?.value ?? '');
    paymentdata.append('payment_date', this.paymentForm.get('payment_date')?.value ?? '');
    paymentdata.append('payment_status', this.paymentForm.get('payment_status')?.value ?? '');
    paymentdata.append('transaction_reference', this.paymentForm.get('transaction_reference')?.value ?? '');
    paymentdata.append('notes', this.paymentForm.get('notes')?.value ?? '');
    paymentdata.append('bill_id', this.billId.toString());
    paymentdata.append('received_by',"1");
   if (this.selectedfile) paymentdata.append('cheque_file', this.selectedfile);
    console.log("works")

      this.paymentposterService.createPayment(paymentdata).subscribe({
  next: (response: any) => {
    console.log('Payment successful:', response.data);
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

  
  
  

