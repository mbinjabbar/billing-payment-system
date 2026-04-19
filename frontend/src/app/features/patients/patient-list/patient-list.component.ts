import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { PatientService } from '../../../core/services/patient.service';

@Component({
  selector: 'app-patient-list',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule],
  templateUrl: './patient-list.component.html',
})
export class PatientListComponent {
  private patientService = inject(PatientService);

  patients    = signal<any[]>([]);
  loading     = signal(true);
  error       = signal('');
  searchQuery = signal('');

  // ── Pagination ────────────────────────────────────────────────────────────
  currentPage  = signal(1);
  totalPages   = signal(1);
  totalItems   = signal(0);
  perPage      = signal(10);
  from         = computed(() => ((this.currentPage() - 1) * this.perPage()) + 1);
  to           = computed(() => Math.min(this.currentPage() * this.perPage(), this.totalItems()));
  visiblePages = computed(() => this.buildVisiblePages(this.totalPages(), this.currentPage()));

  ngOnInit() {
    this.loadPatients(1);
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

  loadPatients(page: number) {
    this.loading.set(true);
    this.patientService.getPatients(page, this.searchQuery()).subscribe({
      next: (res: any) => {
        this.patients.set(res.data ?? []);
        const meta = res.meta;
        if (meta) {
          this.currentPage.set(meta.current_page);
          this.totalPages.set(meta.last_page);
          this.totalItems.set(meta.total);
          this.perPage.set(meta.per_page);
        }
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Failed to load patients.');
        this.loading.set(false);
      }
    });
  }

  onSearch() { this.loadPatients(1); }

  clearSearch() {
    this.searchQuery.set('');
    this.loadPatients(1);
  }

  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.loadPatients(page);
  }

  getGenderClass(gender: string): string {
    switch (gender?.toLowerCase()) {
      case 'male':   return 'bg-blue-100 text-blue-700';
      case 'female': return 'bg-pink-100 text-pink-700';
      default:       return 'bg-gray-100 text-gray-600';
    }
  }

  calculateAge(dob: string): number | string {
    if (!dob) return '—';
    const diff = Date.now() - new Date(dob).getTime();
    return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
  }
}