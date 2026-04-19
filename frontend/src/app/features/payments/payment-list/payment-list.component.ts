import { Component, inject, signal, computed } from '@angular/core';
import { PaymentPosterService } from '../../../core/services/payment-poster.service';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-payment-list',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule],
  templateUrl: './payment-list.component.html',
})
export class PaymentListComponent {
  private paymentService = inject(PaymentPosterService);
  private authService    = inject(AuthService);

  payments    = signal<any>({});
  loading     = signal(false);
  exporting   = signal(false);
  currentPage = signal(1);
  role        = signal<any>(this.authService.getRole());

  // ── Delete confirmation ───────────────────────────────────────────────────
  confirmDeleteId = signal<number | null>(null);

  // ── Refund confirmation ───────────────────────────────────────────────────
  confirmRefundId = signal<number | null>(null);

  filterForm = new FormGroup({
    bill_id:        new FormControl(''),
    payment_mode:   new FormControl(''),
    payment_status: new FormControl(''),
    from_date:      new FormControl(''),
    to_date:        new FormControl(''),
  });

  // ── Computed pagination ───────────────────────────────────────────────────
  totalItems = computed(() => this.payments()?.meta?.total    ?? 0);
  totalPages = computed(() => this.payments()?.meta?.last_page ?? 1);
  from       = computed(() => this.payments()?.meta?.from      ?? 0);
  to         = computed(() => this.payments()?.meta?.to        ?? 0);
  list       = computed(() => this.payments()?.data            ?? []);

  ngOnInit() {
    this.fetchPayments();
  }

  // ── Fetch ─────────────────────────────────────────────────────────────────
  fetchPayments(page: number = 1) {
    this.loading.set(true);
    const filters = { ...this.cleanFilters(this.filterForm.value), page };

    this.paymentService.getPayments(filters).subscribe({
      next: (res: any) => {
        this.payments.set(res);
        this.currentPage.set(res?.meta?.current_page ?? 1);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  applyFilters() { this.fetchPayments(1); }

  resetFilters() {
    this.filterForm.reset();
    this.fetchPayments(1);
  }

  // ── Pagination ────────────────────────────────────────────────────────────
  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.fetchPayments(page);
  }

  visiblePages(): (number | string)[] {
    const total   = this.totalPages();
    const current = this.currentPage();
    const pages: (number | string)[] = [];

    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        if (i > 1 && i < total) pages.push(i);
      }
      if (current < total - 2) pages.push('...');
      pages.push(total);
    }
    return pages;
  }

  // ── Delete ────────────────────────────────────────────────────────────────
  confirmDelete(id: number) { this.confirmDeleteId.set(id); }
  cancelDelete()             { this.confirmDeleteId.set(null); }

  executeDelete() {
    const id = this.confirmDeleteId();
    if (!id) return;
    this.paymentService.deletePayment(id).subscribe({
      next: () => {
        this.confirmDeleteId.set(null);
        this.fetchPayments(this.currentPage());
      },
      error: () => this.confirmDeleteId.set(null),
    });
  }

  // ── Refund — show confirmation first ──────────────────────────────────────
  confirmRefund(id: number) { this.confirmRefundId.set(id); }
  cancelRefund()             { this.confirmRefundId.set(null); }

  executeRefund() {
    const id = this.confirmRefundId();
    if (!id) return;
    this.paymentService.refundPayment(id).subscribe({
      next: () => {
        this.confirmRefundId.set(null);
        this.fetchPayments(this.currentPage());
      },
      error: (err) => {
        console.error(err.error?.message || 'Failed to refund payment.');
        this.confirmRefundId.set(null);
      }
    });
  }

  // ── Export ────────────────────────────────────────────────────────────────
  exportPayments() {
    this.exporting.set(true);
    const filters = this.cleanFilters(this.filterForm.value);

    this.paymentService.exportPayments(filters).subscribe({
      next: (res: any) => {
        const blob = new Blob([res], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        const url = window.URL.createObjectURL(blob);
        const a   = document.createElement('a');
        a.href     = url;
        a.download = 'payments.xlsx';
        a.click();
        window.URL.revokeObjectURL(url);
        this.exporting.set(false);
      },
      error: () => this.exporting.set(false),
    });
  }

  // ── Helpers ───────────────────────────────────────────────────────────────
  private cleanFilters(filters: any): any {
    const cleaned: any = {};
    Object.keys(filters).forEach(key => {
      const val = filters[key];
      if (val !== null && val !== '' && val !== undefined) cleaned[key] = val;
    });
    return cleaned;
  }

  getPaymentModeClass(mode: string): string {
    switch (mode) {
      case 'Cash':           return 'bg-green-100 text-green-700';
      case 'Check':          return 'bg-blue-100 text-blue-700';
      case 'Bank Transfer':  return 'bg-indigo-100 text-indigo-700';
      case 'Credit Card':    return 'bg-purple-100 text-purple-700';
      case 'Debit Card':     return 'bg-violet-100 text-violet-700';
      case 'Insurance':      return 'bg-cyan-100 text-cyan-700';
      case 'Online Payment': return 'bg-orange-100 text-orange-700';
      default:               return 'bg-gray-100 text-gray-700';
    }
  }

  getPaymentStatusClass(status: string): string {
    switch (status) {
      case 'Completed': return 'bg-green-100 text-green-700';
      case 'Pending':   return 'bg-orange-100 text-orange-700';
      case 'Failed':    return 'bg-red-100 text-red-700';
      case 'Refunded':  return 'bg-gray-200 text-gray-600';
      default:          return 'bg-gray-100 text-gray-700';
    }
  }
}