import { Component, inject, signal, computed } from '@angular/core';
import { DocumentService } from '../../../core/services/document.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-document-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './document-list.component.html',
  styleUrl: './document-list.component.css'
})
export class DocumentListComponent {
  private documentService = inject(DocumentService);

  documents   = signal<any>({});
  loading     = signal(false);
  currentPage = signal(1);
  activeType  = signal('');

  // ── Computed pagination ──────────────────────────────────────────────────
  list       = computed(() => this.documents()?.data  ?? []);
  totalItems = computed(() => this.documents()?.meta?.total    ?? 0);
  totalPages = computed(() => this.documents()?.meta?.last_page ?? 1);
  from       = computed(() => this.documents()?.meta?.from      ?? 0);
  to         = computed(() => this.documents()?.meta?.to        ?? 0);

  readonly docTypes = ['NF2 Form', 'Invoice', 'Receipt', 'Cheque Image', 'Supporting Document'];

  ngOnInit() {
    this.loadDocuments();
  }

  loadDocuments(page: number = 1) {
    this.loading.set(true);
    const params: any = { page };
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
    this.activeType.set(type);
    this.loadDocuments(1);
  }

  // ── Pagination ───────────────────────────────────────────────────────────
  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.loadDocuments(page);
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

  // ── UI helpers ───────────────────────────────────────────────────────────
  getDocTypeClass(type: string): string {
    switch (type) {
      case 'NF2 Form':            return 'bg-orange-100 text-orange-700';
      case 'Invoice':             return 'bg-cyan-100 text-cyan-700';
      case 'Receipt':             return 'bg-green-100 text-green-700';
      case 'Cheque Image':        return 'bg-purple-100 text-purple-700';
      case 'Supporting Document': return 'bg-gray-100 text-gray-600';
      default:                    return 'bg-gray-100 text-gray-600';
    }
  }

  getDocIcon(type: string): string {
    switch (type) {
      case 'NF2 Form':     return 'article';
      case 'Invoice':      return 'receipt_long';
      case 'Receipt':      return 'receipt';
      case 'Cheque Image': return 'image';
      default:             return 'description';
    }
  }

  // Bills PDF goes through the Laravel download endpoint
  // Cheque images are in public storage
  getDownloadUrl(doc: any): string {
    if (doc.document_type === 'Invoice' || doc.document_type === 'NF2 Form') {
      return `http://localhost:8000/api/bills/pdf/${doc.bill_id}`;
    }
    return `http://localhost:8000/storage/${doc.file_path}`;
  }
}