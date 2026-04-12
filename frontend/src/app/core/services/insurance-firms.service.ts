import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class InsuranceFirmsService {
  private apiUrl = 'http://localhost:8000/api';
  private http   = inject(HttpClient);

  getInsuranceFirms() {
    return this.http.get(`${this.apiUrl}/insurancefirms`);
  }

  getInsuranceFirmById(id: number) {
    return this.http.get(`${this.apiUrl}/insurancefirms/${id}`);
  }

  createInsuranceFirm(payload: any) {
    return this.http.post(`${this.apiUrl}/insurancefirms`, payload);
  }

  updateInsuranceFirm(id: number, payload: any) {
    return this.http.put(`${this.apiUrl}/insurancefirms/${id}`, payload);
  }

  deleteInsuranceFirm(id: number) {
    return this.http.delete(`${this.apiUrl}/insurancefirms/${id}`);
  }
}