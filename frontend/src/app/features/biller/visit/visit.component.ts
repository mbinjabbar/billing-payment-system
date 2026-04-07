import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { VisitService } from '../../../core/services/visit.service';

@Component({
  selector: 'app-visit',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './visit.component.html',
})
export class VisitComponent {
  private visitService = inject(VisitService);

  visits = signal<any>({ data: [] });

  totalPending = computed(() =>
    this.visits().data.reduce((sum: number, v: any) =>
      sum + (v.bill === null ? 1 : 0), 0)
  );

  unbilledCount = computed(() =>
    this.visits().data.filter((v: any) => v.bill === null).length
  );

  billedCount = computed(() =>
    this.visits().data.filter((v: any) => v.bill !== null).length
  );

  ngOnInit() {
    this.loadVisits(1);
  }

  loadVisits(page: number) {
    this.visitService.getVisits(page).subscribe((res: any) => {
      this.visits.set(res);

      this.currentPage.set(res.meta.current_page);
      this.totalPages.set(res.meta.last_page);
      this.totalItems.set(res.meta.total);
      this.perPage.set(res.meta.per_page);
      this.from.set(res.meta.from);
      this.to.set(res.meta.to);
    });
  }

  getVisitStatusClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'completed': return 'bg-green-200 text-green-700';
      case 'pending': return 'bg-orange-200 text-orange-700';
      case 'cancelled': return 'bg-surface-container-high text-on-surface-variant';
      default: return 'bg-surface-container-high text-on-surface-variant';
    }
  }

  getBillingStatusClass(billed: boolean): string {
    return billed
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

  currentPage = signal(1);
  totalPages = signal(1);
  totalItems = signal(0);
  perPage = signal(10);
  from = signal(0);
  to = signal(0);

  visiblePages = computed(() => {
    const total = this.totalPages();
    const current = this.currentPage();
    const pages: (number | string)[] = [];

    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      const start = Math.max(2, current - 1);
      const end = Math.min(total - 1, current + 1);
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

  goToFirst() { this.loadVisits(1); }
  goToLast()  { this.loadVisits(this.totalPages()); }
  goToPrev()  { this.loadVisits(this.currentPage() - 1); }
  goToNext()  { this.loadVisits(this.currentPage() + 1); }

  protected Math = Math;
}