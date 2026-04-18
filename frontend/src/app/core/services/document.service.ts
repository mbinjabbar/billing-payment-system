import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class DocumentService {
  private apiUrl = environment.laravelApiUrl;
  private http = inject(HttpClient);

  getDocuments(params: any = {}) {
    const stringParams: any = {};
    Object.keys(params).forEach((key) => {
      {
        stringParams[key] = String(params[key]);
      }
    });
    return this.http.get(`${this.apiUrl}/documents`, { params: stringParams });
  }

  downloadDocument(document: any) {
    let url = '';
    switch (document.document_type) {
      case 'Invoice':
        url = `${this.apiUrl}/bills/invoice/${document.bill_id}`;
        break;
      case 'NF2 Form':
        url = `${this.apiUrl}/bills/nf2/${document.bill_id}`;
        break;
      case 'Cheque Image':
        url = `${this.apiUrl}/documents/cheque/${document.id}`;
        break;
      case 'Receipt':
        url = `${this.apiUrl}/payments/receipt/${document.payment_id}`;
        break;
      default:
        url = `${this.apiUrl}/documents/cheque/${document.id}`;
    }
    const link = window.document.createElement('a');
    link.href = url;
    link.click();
  }
}
