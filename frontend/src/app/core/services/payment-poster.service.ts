import { Injectable,inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';


@Injectable({
  providedIn: 'root'
})
export class PaymentPosterService {
    private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getPayments()
  {
    return this.http.get(`${this.apiUrl}/payments`)
  }
  
  getPaymentById(paymentId:number){
    return this.http.get(`${this.apiUrl}/payments/${paymentId}`)
  }

  createPayment(payload: any){
    return this.http.post(`${this.apiUrl}/payments`, payload);
  }
  
}
