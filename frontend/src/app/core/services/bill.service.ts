import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class BillService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getBills() {
    return this.http.get(`${this.apiUrl}/bills`);
  }

  createBill(payload: any) {
    return this.http.post(`${this.apiUrl}/bills`, payload);
  }
}
