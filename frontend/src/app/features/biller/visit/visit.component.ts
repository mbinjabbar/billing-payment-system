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
    this.visitService.getVisits().subscribe((data) => this.visits.set(data));
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
}