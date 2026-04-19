import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl, Validators } from '@angular/forms';
import { InsuranceFirmsService } from '../../../../core/services/insurance-firms.service';

@Component({
  selector: 'app-insurance-firms',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './insurance-firms.component.html',
})
export class InsuranceFirmsComponent {
  private insuranceService = inject(InsuranceFirmsService);

  insuranceFirms   = signal<any[]>([]);
  editingInsurance = signal<any>(null);
  loading          = signal(false);
  error            = signal('');
  success          = signal('');
  confirmDeleteId  = signal<number | null>(null);

  // ── Pagination ────────────────────────────────────────────────────────────
  currentPage  = signal(1);
  totalPages   = signal(1);
  totalItems   = signal(0);
  perPage      = signal(10);
  from         = computed(() => ((this.currentPage() - 1) * this.perPage()) + 1);
  to           = computed(() => Math.min(this.currentPage() * this.perPage(), this.totalItems()));
  visiblePages = computed(() => this.buildVisiblePages(this.totalPages(), this.currentPage()));

  insuranceForm = new FormGroup({
    name:           new FormControl('', Validators.required),
    firm_type:      new FormControl('Health', Validators.required),
    carrier_code:   new FormControl(''),
    contact_person: new FormControl(''),
    email:          new FormControl('', Validators.email),
    phone:          new FormControl(''),
    address:        new FormControl(''),
    is_active:      new FormControl(true),
  });

  ngOnInit() {
    this.loadInsuranceFirms(1);
  }

  private buildVisiblePages(total: number, current: number): (number | string)[] {
    const pages: (number | string)[] = [];
    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
      return pages;
    }
    pages.push(1);
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i);
    }
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
  }

  loadInsuranceFirms(page: number) {
    this.insuranceService.getInsuranceFirms(false, page).subscribe({
      next: (res: any) => {
        this.insuranceFirms.set(res.data ?? []);
        const meta = res.meta;
        if (meta) {
          this.currentPage.set(meta.current_page);
          this.totalPages.set(meta.last_page);
          this.totalItems.set(meta.total);
          this.perPage.set(meta.per_page);
        }
      },
      error: () => this.error.set('Failed to load insurance firms.'),
    });
  }

  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.loadInsuranceFirms(page);
  }

  editInsurance(f: any) {
    this.editingInsurance.set(f);
    this.insuranceForm.patchValue(f);
    this.error.set('');
    this.success.set('');
  }

  cancelEdit() {
    this.editingInsurance.set(null);
    this.insuranceForm.reset({ firm_type: 'Health', is_active: true });
  }

  save() {
    if (this.insuranceForm.invalid) {
      this.insuranceForm.markAllAsTouched();
      return;
    }
    this.loading.set(true);
    this.error.set('');
    this.success.set('');

    const payload = this.insuranceForm.value;
    const editing = this.editingInsurance();

    const req = editing
      ? this.insuranceService.updateInsuranceFirm(editing.id, payload)
      : this.insuranceService.createInsuranceFirm(payload);

    req.subscribe({
      next: () => {
        this.success.set(editing ? 'Insurance firm updated.' : 'Insurance firm created.');
        this.cancelEdit();
        this.loadInsuranceFirms(this.currentPage());
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message || 'Failed to save insurance firm.');
        this.loading.set(false);
      }
    });
  }

  confirmDelete(id: number) { this.confirmDeleteId.set(id); }
  cancelDelete()             { this.confirmDeleteId.set(null); }

  executeDelete() {
    const id = this.confirmDeleteId();
    if (!id) return;
    this.insuranceService.deleteInsuranceFirm(id).subscribe({
      next: () => {
        this.cancelDelete();
        this.success.set('Insurance firm deleted.');
        this.loadInsuranceFirms(this.currentPage());
      },
      error: () => {
        this.error.set('Failed to delete. It may be in use by existing bills.');
        this.cancelDelete();
      }
    });
  }

  getFirmTypeClass(type: string): string {
    return type === 'Auto'
      ? 'bg-orange-100 text-orange-700'
      : 'bg-cyan-100 text-cyan-700';
  }
}