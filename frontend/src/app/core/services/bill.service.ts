import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BillService {
  private apiUrl = `${environment.laravelApiUrl}/bills`;
  private http   = inject(HttpClient);

  getBills(filters: any = {}) {
    return this.http.get(this.apiUrl, {
      params: filters
    });
  }

  getBillById(id: number) {
    return this.http.get(`${this.apiUrl}/${id}`);
  }

  createBill(payload: any) {
    return this.http.post(this.apiUrl, payload);
  }

  updateBill(id: number, payload: any) {
    return this.http.put(`${this.apiUrl}/${id}`, payload);
  }

  deleteBill(id: number) {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }

  exportBills(filters: any = {}) {
    return this.http.post(`${this.apiUrl}/export`, filters, {
      responseType: 'blob'
    });
  }

  updateBillStatus(id: number, status: string) {
    return this.http.patch(`${this.apiUrl}/${id}/status`, { status });
  }
}