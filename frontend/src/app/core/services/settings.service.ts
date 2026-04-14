import { HttpClient } from '@angular/common/http';
import { computed, inject, Injectable, signal } from '@angular/core';
import { Title } from '@angular/platform-browser';

@Injectable({ providedIn: 'root' })
export class SettingsService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);
  private settings = signal<any>(null);
  private title = inject(Title);

  appName = computed(() => this.settings()?.clinic_name)

  getSettings() {
    return this.http.get(`${this.apiUrl}/settings`);
  }

  saveSettings(payload: any) {
    return this.http.post(`${this.apiUrl}/settings`, payload);
  }

  load() {
    this.getSettings().subscribe({
      next: (res: any) => {
        this.settings.set(res.data);
        this.title.setTitle(this.appName());
      }
    })
  }
}