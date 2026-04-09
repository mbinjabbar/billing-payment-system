import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DocumentService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getDocuments(){
    return this.http.get(`${this.apiUrl}/documents`)
  }
}
