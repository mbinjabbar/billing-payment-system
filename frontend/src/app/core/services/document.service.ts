import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class DocumentService {
  private apiUrl = environment.laravelApiUrl;
  private http   = inject(HttpClient);

  getDocuments(params: any = {}) {
    const stringParams: any = {};
    Object.keys(params).forEach((key) => {
      stringParams[key] = String(params[key]);
    });
    return this.http.get(`${this.apiUrl}/documents`, { params: stringParams });
  }

  downloadInvoice(billId: number) {
    return this.http.get(`${this.apiUrl}/bills/invoice/${billId}`, {
      responseType: 'blob'
    });
  }

  downloadNF2(billId: number) {
    return this.http.get(`${this.apiUrl}/bills/nf2/${billId}`, {
      responseType: 'blob'
    });
  }

  downloadReceipt(paymentId: number) {
    return this.http.get(`${this.apiUrl}/payments/receipt/${paymentId}`, {
      responseType: 'blob'
    });
  }

  downloadCheque(documentId: number) {
    return this.http.get(`${this.apiUrl}/documents/cheque/${documentId}`, {
      responseType: 'blob'
    });
  }

  downloadDocument(doc: any) {
    switch (doc.document_type) {
      case 'Invoice':
        return this.downloadInvoice(doc.bill_id);
      case 'NF2 Form':
        return this.downloadNF2(doc.bill_id);
      case 'Receipt':
        return this.downloadReceipt(doc.payment_id);
      case 'Cheque Image':
        return this.downloadCheque(doc.id);
      default:
        return this.downloadCheque(doc.id);
    }
  }

  triggerDownload(blob: Blob, filename: string) {
    const url = window.URL.createObjectURL(blob);
    const a   = document.createElement('a');
    a.href     = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
  }
}