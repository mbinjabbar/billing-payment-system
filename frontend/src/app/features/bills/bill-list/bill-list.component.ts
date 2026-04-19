import { Component, signal, computed, OnInit, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { BillService } from '../../../core/services/bill.service';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl } from '@angular/forms';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-bills',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule],
  templateUrl: './bill-list.component.html',
})
export class BillListComponent implements OnInit {

  private billService = inject(BillService);
  private authService = inject(AuthService);

  bills           = signal<any>({});
  loading         = signal(false);
  currentPage     = signal(1);
  role            = computed(() => this.authService.getRole());
  exporting       = signal(false);

  // ── Delete confirmation ───────────────────────────────────────────────────
  confirmDeleteId = signal<number | null>(null);

  // ── Change status confirmation ────────────────────────────────────────────
  confirmStatusId     = signal<number | null>(null);
  confirmStatusValue  = signal<string>('');
  pendingSelectRef: HTMLSelectElement | null = null;

  filterForm = new FormGroup({
    patient_name: new FormControl(''),
    status:       new FormControl(''),
    start_date:   new FormControl(''),
    end_date:     new FormControl(''),
    min_amount:   new FormControl(''),
    max_amount:   new FormControl(''),
  });

  ngOnInit() { this.fetchBills(); }

  // ── Fetch ─────────────────────────────────────────────────────────────────
  fetchBills(page: number = 1) {
    this.loading.set(true);
    const filters = { ...this.cleanFilters(this.filterForm.value), page };

    this.billService.getBills(filters).subscribe({
      next: (res: any) => {
        this.bills.set(res);
        this.currentPage.set(res?.meta?.current_page ?? 1);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }

  applyFilters() { this.fetchBills(1); }

  resetFilters() {
    this.filterForm.reset();
    this.fetchBills(1);
  }

  // ── Delete ────────────────────────────────────────────────────────────────
  confirmDelete(id: number) { this.confirmDeleteId.set(id); }
  cancelDelete()             { this.confirmDeleteId.set(null); }

  executeDelete() {
    const id = this.confirmDeleteId();
    if (!id) return;
    this.billService.deleteBill(id).subscribe({
      next: () => {
        this.confirmDeleteId.set(null);
        this.fetchBills(this.currentPage());
      },
      error: () => this.confirmDeleteId.set(null)
    });
  }

  // ── Change Status — show confirmation first ───────────────────────────────
  overrideStatus(billId: number, event: Event) {
    const select = event.target as HTMLSelectElement;
    const status = select.value;
    if (!status) return;

    // Save reference to reset select if user cancels
    this.pendingSelectRef = select;
    this.confirmStatusId.set(billId);
    this.confirmStatusValue.set(status);
  }

  cancelStatusChange() {
    // Reset select back to empty
    if (this.pendingSelectRef) {
      this.pendingSelectRef.value = '';
      this.pendingSelectRef = null;
    }
    this.confirmStatusId.set(null);
    this.confirmStatusValue.set('');
  }

  executeStatusChange() {
    const id     = this.confirmStatusId();
    const status = this.confirmStatusValue();
    if (!id || !status) return;

    this.billService.updateBillStatus(id, status).subscribe({
      next: () => {
        this.confirmStatusId.set(null);
        this.confirmStatusValue.set('');
        this.pendingSelectRef = null;
        this.fetchBills(this.currentPage());
      },
      error: () => {
        this.cancelStatusChange();
      }
    });
  }

  // ── Export ────────────────────────────────────────────────────────────────
  exportBills() {
    this.exporting.set(true);
    const filters = this.cleanFilters(this.filterForm.value);

    this.billService.exportBills(filters).subscribe({
      next: (res: any) => {
        const blob = new Blob([res], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });
        const url  = window.URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'bills.xlsx';
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

  // ── Pagination ────────────────────────────────────────────────────────────
  totalItems = computed(() => this.bills()?.meta?.total    ?? 0);
  totalPages = computed(() => this.bills()?.meta?.last_page ?? 1);
  from       = computed(() => this.bills()?.meta?.from      ?? 0);
  to         = computed(() => this.bills()?.meta?.to        ?? 0);

  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.fetchBills(page);
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

  // ── UI helpers ────────────────────────────────────────────────────────────
  getBillStatusClass(status: string): string {
    switch (status) {
      case 'Paid':        return 'bg-green-100 text-green-700';
      case 'Pending':     return 'bg-orange-100 text-orange-700';
      case 'Partial':     return 'bg-blue-100 text-blue-700';
      case 'Cancelled':   return 'bg-gray-200 text-gray-600';
      case 'Draft':       return 'bg-purple-100 text-purple-700';
      case 'Written Off': return 'bg-red-100 text-red-700';
      default:            return 'bg-gray-100 text-gray-700';
    }
  }

  isOverdue(date: string): boolean {
    if (!date) return false;
    return new Date(date) < new Date();
  }
}