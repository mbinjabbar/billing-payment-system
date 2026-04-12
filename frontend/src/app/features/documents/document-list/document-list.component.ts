import { Component, inject, signal, computed } from '@angular/core';
import { DocumentService } from '../../../core/services/document.service';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-document-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './document-list.component.html',
  styleUrl: './document-list.component.css',
})
export class DocumentListComponent {
  private documentService = inject(DocumentService);
  private authService = inject(AuthService);

  role = computed(() => this.authService.getRole() ?? '');

  documents = signal<any>({});
  loading = signal(false);
  currentPage = signal(1);
  activeType = signal('');

  // ── Computed pagination ──────────────────────────────────────────────────
  list = computed(() => this.documents()?.data ?? []);
  totalItems = computed(() => this.documents()?.meta?.total ?? 0);
  totalPages = computed(() => this.documents()?.meta?.last_page ?? 1);
  from = computed(() => this.documents()?.meta?.from ?? 0);
  to = computed(() => this.documents()?.meta?.to ?? 0);

  // ── Doc type filter pills per role ───────────────────────────────────────
  visibleDocTypes = computed(() => {
    switch (this.role()) {
      case 'Biller':
        return ['Invoice', 'NF2 Form'];
      case 'Payment Poster':
        return ['Invoice', 'Cheque Image'];
      default:
        return ['Invoice', 'NF2 Form', 'Cheque Image', 'Supporting Document'];
    }
  });

  ngOnInit() {
    this.loadDocuments();
  }

  loadDocuments(page: number = 1) {
    this.loading.set(true);

    const params: any = { page, role: this.role() };
    if (this.activeType()) params.type = this.activeType();

    this.documentService.getDocuments(params).subscribe({
      next: (res: any) => {
        this.documents.set(res);
        this.currentPage.set(res?.meta?.current_page ?? 1);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  filterByType(type: string) {
    // Toggle off if same type clicked again
    this.activeType.set(this.activeType() === type ? '' : type);
    this.loadDocuments(1);
  }

  // ── Pagination ───────────────────────────────────────────────────────────
  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.loadDocuments(page);
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

  downloadDocument(doc: any) {
    this.documentService.downloadDocument(doc);
  }

  // ── UI helpers ───────────────────────────────────────────────────────────
  getDocTypeClass(type: string): string {
    switch (type) {
      case 'Invoice':
        return 'bg-cyan-100 text-cyan-700';
      case 'NF2 Form':
        return 'bg-orange-100 text-orange-700';
      case 'Cheque Image':
        return 'bg-purple-100 text-purple-700';
      case 'Supporting Document':
        return 'bg-gray-100 text-gray-600';
      default:
        return 'bg-gray-100 text-gray-600';
    }
  }

  getDocIcon(type: string): string {
    switch (type) {
      case 'Invoice':
        return 'receipt_long';
      case 'NF2 Form':
        return 'article';
      case 'Cheque Image':
        return 'image';
      case 'Supporting Document':
        return 'folder';
      default:
        return 'description';
    }
  }
}
