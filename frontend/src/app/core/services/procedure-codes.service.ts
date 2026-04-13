import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ProcedureCodesService {
  private apiUrl = 'http://localhost:8000/api';
  private http   = inject(HttpClient);

  getProcedureCodes(activeOnly: boolean = false) {
    let params = new HttpParams();

    if(activeOnly) {
      params = params.set('active_only', true)
    }
    return this.http.get(`${this.apiUrl}/procedurecodes`, { params });
  }

  getProcedureCodeById(id: number) {
    return this.http.get(`${this.apiUrl}/procedurecodes/${id}`);
  }

  createProcedureCode(payload: any) {
    return this.http.post(`${this.apiUrl}/procedurecodes`, payload);
  }

  updateProcedureCode(id: number, payload: any) {
    return this.http.put(`${this.apiUrl}/procedurecodes/${id}`, payload);
  }

  deleteProcedureCode(id: number) {
    return this.http.delete(`${this.apiUrl}/procedurecodes/${id}`);
  }
}