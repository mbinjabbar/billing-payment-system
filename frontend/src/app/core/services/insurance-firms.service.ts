import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class InsuranceFirmsService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getInsuranceFirms() {
    return this.http.get(`${this.apiUrl}/insurancefirms`);
  }
}
