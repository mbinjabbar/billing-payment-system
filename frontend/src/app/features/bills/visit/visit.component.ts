import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { VisitService } from '../../../core/services/visit.service';
import { RouterLink } from '@angular/router';
import { ReactiveFormsModule, FormGroup, FormControl } from '@angular/forms';

@Component({
  selector: 'app-visit',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule],
  templateUrl: './visit.component.html',
})
export class VisitComponent {
  private visitService = inject(VisitService);

  visits  = signal<any>({ data: [] });
  loading = signal(false);

  stats = signal<any>({
    total_visits: 0,
    billed: 0,
    unbilled: 0,
  });

  filterForm = new FormGroup({
    patient_name: new FormControl(''),
    status:       new FormControl(''),
    visit_date:   new FormControl(''),
  });

  // Pagination
  currentPage = signal(1);
  totalPages  = signal(1);
  totalItems  = signal(0);
  perPage     = signal(10);
  from        = signal(0);
  to          = signal(0);

  ngOnInit() {
    this.loadVisits(1);
  }

  loadVisits(page: number) {
    this.loading.set(true);
    const filters = this.cleanFilters(this.filterForm.value);

    this.visitService.getVisits(page, filters).subscribe({
      next: (res: any) => {
        this.visits.set(res);
        this.currentPage.set(res.meta.current_page);
        this.totalPages.set(res.meta.last_page);
        this.totalItems.set(res.meta.total);
        this.perPage.set(res.meta.per_page);
        this.from.set(res.meta.from);
        this.to.set(res.meta.to);
        if (res.stats) this.stats.set(res.stats);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  applyFilters() { this.loadVisits(1); }

  resetFilters() {
    this.filterForm.reset();
    this.loadVisits(1);
  }

  private cleanFilters(filters: any): any {
    const cleaned: any = {};
    Object.keys(filters).forEach(key => {
      const val = filters[key];
      if (val !== null && val !== '' && val !== undefined) cleaned[key] = val;
    });
    return cleaned;
  }

  visiblePages = computed(() => {
    const total   = this.totalPages();
    const current = this.currentPage();
    const pages: (number | string)[] = [];

    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      const start = Math.max(2, current - 1);
      const end   = Math.min(total - 1, current + 1);
      for (let i = start; i <= end; i++) pages.push(i);
      if (current < total - 2) pages.push('...');
      pages.push(total);
    }
    return pages;
  });

  goToPage(page: number | string) {
    if (typeof page !== 'number') return;
    if (page < 1 || page > this.totalPages()) return;
    this.loadVisits(page);
  }

  //  UI helpers
  getVisitStatusClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'completed': return 'bg-green-200 text-green-700';
      case 'pending':   return 'bg-orange-200 text-orange-700';
      case 'cancelled': return 'bg-surface-container-high text-on-surface-variant';
      default:          return 'bg-surface-container-high text-on-surface-variant';
    }
  }

  getBillingStatusClass(bill: any): string {
    return bill !== null
      ? 'bg-green-200 text-green-700'
      : 'bg-error-container/20 text-error';
  }

  getBillingLabel(bill: any): string {
    return bill !== null ? 'Billed' : 'Unbilled';
  }

  isReadyForBilling(visit: any): boolean {
    return visit.status?.toLowerCase() === 'completed' && visit.bill === null;
  }

  isBilled(visit: any): boolean {
    return visit.bill !== null;
  }

  isCancelled(visit: any): boolean {
    return visit.status?.toLowerCase() === 'cancelled';
  }

  protected Math = Math;
}