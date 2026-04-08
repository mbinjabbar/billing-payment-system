import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ProcedureCodesService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getProcedureCodes(){
    return this.http.get(`${this.apiUrl}/procedurecodes`)
  }
}
