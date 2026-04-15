import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';


@Injectable({
  providedIn: 'root'
})
export class PaymentPosterService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);


  getPayments(filters?: any) {
    return this.http.get(`${this.apiUrl}/payments`, {
      params: filters
    });
  }

  getPaymentById(paymentId: number) {
    return this.http.get(`${this.apiUrl}/payments/${paymentId}`)
  }

  createPayment(payment: FormData): Observable<any> {
    return this.http.post(`${this.apiUrl}/payments`, payment);
  }

  updatePayment(paymentId: number, payment: FormData) {
    return this.http.post(`${this.apiUrl}/payments/${paymentId}`, payment);
  }

  refundPayment(id: number) {
    return this.http.patch(`${this.apiUrl}/payments/${id}/refund`, {});
  }

  deletePayment(paymentId: number) {
    return this.http.delete(`${this.apiUrl}/payments/${paymentId}`);
  }

  exportPayments(filters: any) {
    return this.http.post(`${this.apiUrl}/payments/export`, filters, {
      responseType: 'blob'
    });
  }


}
