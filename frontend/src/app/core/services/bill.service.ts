import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class BillService {
  private apiUrl = 'http://localhost:8000/api';
  private http   = inject(HttpClient);

  getBills(filters: any = {}) {
    return this.http.get(`${this.apiUrl}/bills`, { params: filters });
  }

  getBillById(id: number) {
    return this.http.get(`${this.apiUrl}/bills/${id}`);
  }

  createBill(payload: any) {
    return this.http.post(`${this.apiUrl}/bills`, payload);
  }

  updateBill(id: number, payload: any) {
    return this.http.put(`${this.apiUrl}/bills/${id}`, payload);
  }
}