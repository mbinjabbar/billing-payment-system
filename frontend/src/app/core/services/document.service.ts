import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DocumentService {
  private apiUrl = 'http://localhost:8000/api';
  private http   = inject(HttpClient);

  getDocuments(params: any = {}) {
    const stringParams: any = {};
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
        stringParams[key] = String(params[key]);
      }
    });
    return this.http.get(`${this.apiUrl}/documents`, { params: stringParams });
  }
}