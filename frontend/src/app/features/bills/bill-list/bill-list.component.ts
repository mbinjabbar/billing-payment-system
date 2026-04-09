import { Component, signal, computed, OnInit, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { BillService } from '../../../core/services/bill.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-bills',
  imports: [CommonModule, RouterLink],
  templateUrl: './bill-list.component.html',
})
export class BillListComponent implements OnInit {

  private billService = inject(BillService)

  bills = signal<any>({});
  loading = signal(false);
  currentPage = signal(1);

  ngOnInit() {
    this.fetchBills();
  }

  fetchBills(page: number = 1) {
    this.loading.set(true);

    this.billService.getBills(page).subscribe({
      next: (res: any) => {
        this.bills.set(res);
        this.currentPage.set(res?.meta?.current_page ?? 1);
        this.loading.set(false);
      },
      error: (err: any) => {
        console.error('Error fetching bills:', err);
        this.loading.set(false);
      }
    });
  }

  totalItems = computed(() => this.bills()?.meta?.total ?? 0);
  totalPages = computed(() => this.bills()?.meta?.last_page ?? 1);
  from = computed(() => this.bills()?.meta?.from ?? 0);
  to = computed(() => this.bills()?.meta?.to ?? 0);

  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.fetchBills(page);
  }

  goToNext() {
    this.goToPage(this.currentPage() + 1);
  }

  goToPrev() {
    this.goToPage(this.currentPage() - 1);
  }

  goToFirst() {
    this.goToPage(1);
  }

  goToLast() {
    this.goToPage(this.totalPages());
  }

  visiblePages(): (number | string)[] {
    const total = this.totalPages();
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

  getBillStatusClass(status: string): string {
    switch (status) {
      case 'Paid':
        return 'bg-green-100 text-green-700';
      case 'Pending':
        return 'bg-orange-100 text-orange-700';
      case 'Partial':
        return 'bg-blue-100 text-blue-700';
      case 'Cancelled':
        return 'bg-gray-200 text-gray-600';
      case 'Draft':
        return 'bg-purple-100 text-purple-700';
      case 'Written Off':
        return 'bg-red-100 text-red-700';
      default:
        return 'bg-gray-100 text-gray-700';
    }
  }

  isOverdue(date: string): boolean {
    if (!date) return false;
    return new Date(date) < new Date();
  }

}