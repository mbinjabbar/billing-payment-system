import { Component, inject, signal, computed } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FormGroup, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { BillService } from '../../../core/services/bill.service';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-create-payment',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, CommonModule],
  templateUrl: './create-payment.component.html',
})
export class CreatePaymentComponent {
  private route          = inject(ActivatedRoute);
  private router         = inject(Router);
  private paymentService = inject(PaymentPosterService);
  private billService    = inject(BillService);
  private authService    = inject(AuthService);

  // ── State ────────────────────────────────────────────────────────────────
  bill          = signal<any>(null);
  selectedFile  = signal<File | null>(null);
  submitting    = signal(false);
  error         = signal('');
  billId        = 0;
  paymentId     = 0;

  paymentForm = new FormGroup({
    amount_paid:           new FormControl('', [Validators.required, Validators.min(0.01)]),
    payment_mode:          new FormControl('', Validators.required),
    check_number:          new FormControl(''),
    bank_name:             new FormControl(''),
    transaction_reference: new FormControl(''),
    payment_date:          new FormControl('', Validators.required),
    payment_status:        new FormControl('Completed', Validators.required),
    notes:                 new FormControl(''),
  });

  // ── Computed summary values ───────────────────────────────────────────────
  outstanding = computed(() => Number(this.bill()?.outstanding_amount ?? 0));

  payingNow = computed(() => {
    const val = Number(this.paymentForm.get('amount_paid')?.value ?? 0);
    return isNaN(val) ? 0 : val;
  });

  remaining = computed(() => {
    const rem = this.outstanding() - this.payingNow();
    return Math.max(0, rem);
  });

  isEdit = computed(() => this.paymentId > 0);

  // ── Lifecycle ────────────────────────────────────────────────────────────
  ngOnInit() {
    const billId = this.route.snapshot.paramMap.get('billId');
    const editId = this.route.snapshot.paramMap.get('id');

    if (billId) {
      this.billId = parseInt(billId, 10);
      this.loadBill(this.billId);
    }

    if (editId) {
      this.paymentId = parseInt(editId, 10);
      this.loadPayment(this.paymentId);
    }
  }

  loadBill(id: number) {
    this.billService.getBillById(id).subscribe({
      next: (res: any) => this.bill.set(res.data ?? res),
      error: () => this.error.set('Failed to load bill details.'),
    });
  }

  loadPayment(id: number) {
    this.paymentService.getPaymentById(id).subscribe({
      next: (res: any) => {
        const data = res.data;
        if (data.payment_date) {
          data.payment_date = data.payment_date.split('T')[0];
        }
        this.billId = data.bill_id;
        this.loadBill(data.bill_id);
        this.paymentForm.patchValue(data);
      },
      error: () => this.error.set('Failed to load payment details.'),
    });
  }

  // ── File ─────────────────────────────────────────────────────────────────
  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files?.length) {
      this.selectedFile.set(input.files[0]);
    }
  }

  clearFile() {
    this.selectedFile.set(null);
  }

  // ── Submit ────────────────────────────────────────────────────────────────
  onSubmit() {
    if (this.paymentForm.invalid) {
      this.paymentForm.markAllAsTouched();
      return;
    }

    // Prevent overpayment
    if (!this.isEdit() && this.payingNow() > this.outstanding()) {
      this.error.set(`Amount cannot exceed outstanding balance of $${this.outstanding().toFixed(2)}`);
      return;
    }

    this.submitting.set(true);
    this.error.set('');

    const fd = new FormData();
    fd.append('bill_id',               this.billId.toString());
    fd.append('amount_paid',           this.paymentForm.get('amount_paid')?.value           ?? '');
    fd.append('payment_mode',          this.paymentForm.get('payment_mode')?.value          ?? '');
    fd.append('check_number',          this.paymentForm.get('check_number')?.value          ?? '');
    fd.append('bank_name',             this.paymentForm.get('bank_name')?.value             ?? '');
    fd.append('transaction_reference', this.paymentForm.get('transaction_reference')?.value ?? '');
    fd.append('payment_date',          this.paymentForm.get('payment_date')?.value          ?? '');
    fd.append('payment_status',        this.paymentForm.get('payment_status')?.value        ?? '');
    fd.append('notes',                 this.paymentForm.get('notes')?.value                 ?? '');

    const file = this.selectedFile();
    if (file) fd.append('cheque_file', file);

    if (this.isEdit()) {
      fd.append('_method', 'PUT');
      this.paymentService.updatePayment(this.paymentId, fd).subscribe({
        next: () => this.router.navigate(['/payments/payment-list']),
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to update payment.');
          this.submitting.set(false);
        },
      });
    } else {
      fd.append('received_by', String(this.authService.getUserId()));
      this.paymentService.createPayment(fd).subscribe({
        next: () => this.router.navigate(['/payments/payment-list']),
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to post payment.');
          this.submitting.set(false);
        },
      });
    }
  }
}