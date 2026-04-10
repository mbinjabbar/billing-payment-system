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
    console.log(id);
  }

   onfileselected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
      this.selectedfile = input.files[0];
    }
  }


   onSubmit() {
    if (!this.paymentForm.valid) return;
    if (!this.selectedfile) {
      alert("Please upload a chequee file");
      return;
    }

    const paymentdata = new FormData();
    paymentdata.append('amount_paid', this.paymentForm.get('amount_paid')?.value ?? '');
    paymentdata.append('payment_mode', this.paymentForm.get('payment_mode')?.value ?? '');
    paymentdata.append('check_number', this.paymentForm.get('check_number')?.value ?? '');
    paymentdata.append('bank_name', this.paymentForm.get('bank_name')?.value ?? '');
    paymentdata.append('payment_date', this.paymentForm.get('payment_date')?.value ?? '');
    paymentdata.append('payment_status', this.paymentForm.get('payment_status')?.value ?? '');
    paymentdata.append('notes', this.paymentForm.get('notes')?.value ?? '');
    if (this.selectedfile) paymentdata.append('payment', this.selectedfile);

      this.paymentposterService.createPayment(paymentdata).subscribe({
      next:  (response: any) => {
        console.log(response.data);
      },
        error: (err) => console.log(err)
      });
    }
  }

  
  
  

