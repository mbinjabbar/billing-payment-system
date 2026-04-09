import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class BillService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getBills(page: number = 1) {
    return this.http.get(`${this.apiUrl}/bills?page=${page}`);
  }

  getBillById(id: number) {
    return this.http.get(`${this.apiUrl}/bills/${id}`);
  }

  createBill(payload: any) {
    return this.http.post(`${this.apiUrl}/bills`, payload);
  }
}
