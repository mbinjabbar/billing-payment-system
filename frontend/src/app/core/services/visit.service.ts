import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class VisitService {
  private apiUrl = `${environment.laravelApiUrl}/visits`;
  private http   = inject(HttpClient);

  getVisits(page: number = 1, filters: any = {}) {
    return this.http.get(this.apiUrl, {
      params: { ...filters, page }
    });
  }

  getVisitById(visitId: number) {
    return this.http.get(`${this.apiUrl}/${visitId}`);
  }
}