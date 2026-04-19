import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl, Validators } from '@angular/forms';
import { ProcedureCodesService } from '../../../../core/services/procedure-codes.service';

@Component({
  selector: 'app-procedure-codes',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './procedure-codes.component.html',
})
export class ProcedureCodesComponent {
  private procedureService = inject(ProcedureCodesService);

  // State
  procedures = signal<any[]>([]);
  editingProcedure = signal<any>(null);
  loading = signal(false);
  error = signal('');
  success = signal('');
  confirmDeleteId = signal<number | null>(null);

  // Pagination
  currentPage = signal(1);
  totalPages = signal(1);
  totalItems = signal(0);
  perPage = signal(10);

  from = computed(() =>
    (this.currentPage() - 1) * this.perPage() + 1
  );

  to = computed(() =>
    Math.min(this.currentPage() * this.perPage(), this.totalItems())
  );

  visiblePages = computed(() =>
    this.buildVisiblePages(this.totalPages(), this.currentPage())
  );

  // Form
  procedureForm = new FormGroup({
    code: new FormControl('', Validators.required),
    name: new FormControl('', Validators.required),
    standard_charge: new FormControl('', [Validators.required, Validators.min(0)]),
    is_active: new FormControl(true),
  });

  ngOnInit() {
    this.loadProcedures(1);
  }

  loadProcedures(page: number) {
    this.procedureService.getProcedureCodes(false, page).subscribe({
      next: (res: any) => {
        this.procedures.set(res.data ?? []);

        const meta = res.meta;
        if (meta) {
          this.currentPage.set(meta.current_page);
          this.totalPages.set(meta.last_page);
          this.totalItems.set(meta.total);
          this.perPage.set(meta.per_page);
        }
      },
      error: () => this.error.set('Failed to load procedure codes.'),
    });
  }

  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.loadProcedures(page);
  }

  // Create / Update
  save() {
    if (this.procedureForm.invalid) {
      this.procedureForm.markAllAsTouched();
      return;
    }

    this.loading.set(true);
    this.error.set('');
    this.success.set('');

    const payload = this.procedureForm.value;
    const editing = this.editingProcedure();

    const req = editing
      ? this.procedureService.updateProcedureCode(editing.id, payload)
      : this.procedureService.createProcedureCode(payload);

    req.subscribe({
      next: () => {
        this.success.set(editing ? 'Procedure code updated.' : 'Procedure code created.');
        this.cancelEdit();
        this.loadProcedures(this.currentPage());
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message || 'Failed to save procedure code.');
        this.loading.set(false);
      }
    });
  }

  // Edit flow
  editProcedure(p: any) {
    this.editingProcedure.set(p);

    this.procedureForm.patchValue({
      code: p.code,
      name: p.name,
      standard_charge: p.standard_charge,
      is_active: p.is_active,
    });

    this.error.set('');
    this.success.set('');
  }

  cancelEdit() {
    this.editingProcedure.set(null);
    this.procedureForm.reset({ is_active: true });
  }

  // Delete flow
  confirmDelete(id: number) {
    this.confirmDeleteId.set(id);
  }

  cancelDelete() {
    this.confirmDeleteId.set(null);
  }

  executeDelete() {
    const id = this.confirmDeleteId();
    if (!id) return;

    this.procedureService.deleteProcedureCode(id).subscribe({
      next: () => {
        this.cancelDelete();
        this.success.set('Procedure code deleted.');
        this.loadProcedures(this.currentPage());
      },
      error: () => {
        this.error.set('Failed to delete. It may be in use by existing bills.');
        this.cancelDelete();
      }
    });
  }

  // UI Helpers
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
}