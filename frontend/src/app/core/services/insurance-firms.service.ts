import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class InsuranceFirmsService {
  private apiUrl = `${environment.laravelApiUrl}/insurancefirms`;
  private http = inject(HttpClient);

  getInsuranceFirms(activeOnly: boolean = false, page: number = 1) {
    let params = new HttpParams();

    if (activeOnly) {
      params = params.set('active_only', true)
    } else {
      params = params.set('page', page);
    }
    return this.http.get(this.apiUrl, { params });
  }

  getInsuranceFirmById(id: number) {
    return this.http.get(`${this.apiUrl}/${id}`);
  }

  createInsuranceFirm(payload: any) {
    return this.http.post(this.apiUrl, payload);
  }

  updateInsuranceFirm(id: number, payload: any) {
    return this.http.put(`${this.apiUrl}/${id}`, payload);
  }

  deleteInsuranceFirm(id: number) {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}