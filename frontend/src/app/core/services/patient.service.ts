import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class PatientService {
  private apiUrl = `${environment.laravelApiUrl}/patients`;
  private http   = inject(HttpClient);

  getPatients(page: number = 1, search: string = '') {
    const params: any = { page };
    if (search) params['search'] = search;
    return this.http.get(this.apiUrl, { params });
  }

  getPatientById(id: number) {
    return this.http.get(`${this.apiUrl}/${id}`);
  }
}