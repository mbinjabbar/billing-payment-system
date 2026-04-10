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
    if (this.selectedfile) paymentdata.append('payment', this.selectedfile);
  
      this.paymentposterService.createPayment(paymentdata).subscribe({
      next:  (response: any) => {response.data},
        error: (err) => console.log(err)
      });
    }
  }

  
  
  

