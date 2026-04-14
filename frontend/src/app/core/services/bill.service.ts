import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class BillService {
  private apiUrl = 'http://localhost:8000/api';
  private http   = inject(HttpClient);

  getBills(filters: any = {}) {
    const params: any = {};
    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== '' && filters[key] !== undefined) {
        params[key] = String(filters[key]);
      }
    });
    return this.http.get(`${this.apiUrl}/bills`, { params });
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

  deleteBill(id: number) {
    return this.http.delete(`${this.apiUrl}/bills/${id}`);
  }

  exportBills(filters: any = {}) {
    return this.http.post(`${this.apiUrl}/bills/export`, filters, {
      responseType: 'blob'
    });
  }

  updateBillStatus(id: number, status: string) {
    return this.http.patch(`${this.apiUrl}/bills/${id}/status`, { status });
  }
}