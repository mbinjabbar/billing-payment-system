import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class SettingsService {
  private apiUrl = 'http://localhost:8000/api';
  private http   = inject(HttpClient);

  getSettings() {
    return this.http.get(`${this.apiUrl}/settings`);
  }

  saveSettings(payload: any) {
    return this.http.post(`${this.apiUrl}/settings`, payload);
  }
}