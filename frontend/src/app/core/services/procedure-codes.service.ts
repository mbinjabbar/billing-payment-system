import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment.development';

@Injectable({
  providedIn: 'root'
})
export class ProcedureCodesService {
  private apiUrl = `${environment.laravelApiUrl}/procedurecodes`;
  private http   = inject(HttpClient);

  getProcedureCodes(activeOnly: boolean = false) {
    let params = new HttpParams();

    if(activeOnly) {
      params = params.set('active_only', true)
    }
    return this.http.get(this.apiUrl, { params });
  }

  getProcedureCodeById(id: number) {
    return this.http.get(`${this.apiUrl}/${id}`);
  }

  createProcedureCode(payload: any) {
    return this.http.post(this.apiUrl, payload);
  }

  updateProcedureCode(id: number, payload: any) {
    return this.http.put(`${this.apiUrl}/${id}`, payload);
  }

  deleteProcedureCode(id: number) {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}