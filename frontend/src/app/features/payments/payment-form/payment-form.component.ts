import { Component, inject, signal, computed } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FormGroup, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { BillService } from '../../../core/services/bill.service';
import { AuthService } from '../../../core/services/auth.service';
import { toSignal } from '@angular/core/rxjs-interop';

@Component({
  selector: 'app-payment-form',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule, CommonModule],
  templateUrl: './payment-form.component.html',
})
export class PaymentFormComponent {
  private route          = inject(ActivatedRoute);
  private router         = inject(Router);
  private paymentService = inject(PaymentPosterService);
  private billService    = inject(BillService);
  private authService    = inject(AuthService);

  // ── State ────────────────────────────────────────────────────────────────
  bill         = signal<any>(null);
  selectedFile = signal<File | null>(null);
  submitting   = signal(false);
  error        = signal('');
  billId       = 0;
  paymentId    = 0;

  // Tracks selected payment mode for conditional rendering
  paymentMode  = signal('');

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

  // ── Computed ─────────────────────────────────────────────────────────────
  outstanding = computed(() => Number(this.bill()?.outstanding_amount ?? 0));
  
  private amountPaidValue = toSignal(
    this.paymentForm.get('amount_paid')!.valueChanges,
    { initialValue: ''}
  )

  payingNow = computed(() => {
    const val = Number(this.amountPaidValue());
    return isNaN(val) ? 0 : val;
  });

  remaining = computed(() => {
    const rem = this.outstanding() - this.payingNow();
    return Math.max(0, rem);
  });

  isEdit = computed(() => this.paymentId > 0);

  // ── Conditional field visibility ─────────────────────────────────────────
  showChequeFields = computed(() => this.paymentMode() === 'Check');

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

    // Track payment mode changes for conditional rendering
    this.paymentForm.get('payment_mode')?.valueChanges.subscribe(val => {
      this.paymentMode.set(val ?? '');
      // Clear mode-specific fields when switching modes
      this.selectedFile.set(null);
      this.paymentForm.patchValue({
        check_number:          '',
        bank_name:             '',
        transaction_reference: '',
      }, { emitEvent: false });
    });
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
        // Set paymentMode signal when editing so conditional fields render
        this.paymentMode.set(data.payment_mode ?? '');
      },
      error: () => this.error.set('Failed to load payment details.'),
    });
  }

  // ── File ─────────────────────────────────────────────────────────────────
  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    const file = input.files[0];
    const allowedTypes = [
      'application/pdf',
      'image/jpeg',
      'image/png',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!allowedTypes.includes(file.type)) {
      this.error.set('Invalid file type. Allowed: PDF, JPG, PNG, DOCX');
      input.value = '';
      return;
    }

    if (file.size > maxSize) {
      this.error.set('File size cannot exceed 5MB');
      input.value = '';
      return;
    }

    this.error.set('');
    this.selectedFile.set(file);
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
        next: () => this.router.navigate(['/payments']),
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to update payment.');
          this.submitting.set(false);
        },
      });
    } else {
      fd.append('received_by', String(this.authService.getUserId()));
      this.paymentService.createPayment(fd).subscribe({
        next: () => this.router.navigate(['/payments']),
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to post payment.');
          this.submitting.set(false);
        },
      });
    }
  }
}