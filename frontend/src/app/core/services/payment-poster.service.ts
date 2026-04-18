import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';


@Injectable({
  providedIn: 'root'
})
export class PaymentPosterService {
    private apiUrl = `${environment.laravelApiUrl}/payments`;
  private http = inject(HttpClient);


  getPayments(filters?: any) {
    return this.http.get(this.apiUrl, {
      params: filters
    });
  }

  getPaymentById(paymentId: number) {
    return this.http.get(`${this.apiUrl}/${paymentId}`)
  }

  createPayment(payment: FormData): Observable<any> {
    return this.http.post(this.apiUrl, payment);
  }

  updatePayment(paymentId: number, payment: FormData) {
    return this.http.put(`${this.apiUrl}/${paymentId}`, payment);
  }

  refundPayment(id: number) {
    return this.http.patch(`${this.apiUrl}/${id}/refund`, {});
  }

  deletePayment(paymentId: number) {
    return this.http.delete(`${this.apiUrl}/${paymentId}`);
  }

  exportPayments(filters: any) {
    return this.http.post(`${this.apiUrl}/export`, filters, {
      responseType: 'blob'
    });
  }


}
