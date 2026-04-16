import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { environment } from '../../../environments/environment.development';

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = `${environment.nodeApiUrl}/users`;
  private http   = inject(HttpClient);

  getUsers() {
    return this.http.get(this.apiUrl);
  }

  getUserById(id: number) {
    return this.http.get(`${this.apiUrl}/${id}`);
  }

  createUser(payload: any) {
    return this.http.post(this.apiUrl, payload);
  }

  updateUser(id: number, payload: any) {
    return this.http.patch(`${this.apiUrl}/${id}`, payload);
  }

  deleteUser(id: number) {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}