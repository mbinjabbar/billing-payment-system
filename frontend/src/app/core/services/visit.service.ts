import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class VisitService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  getVisits(page: number = 1){
    return this.http.get(`${this.apiUrl}/visits?page=${page}`)
  }
}
