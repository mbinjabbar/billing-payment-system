import { HttpClient } from '@angular/common/http';
import { computed, inject, Injectable, signal } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class SettingsService {
  private apiUrl = `${environment.laravelApiUrl}/settings`;
  private http = inject(HttpClient);
  private settings = signal<any>(null);
  private title = inject(Title);

  appName = computed(() => this.settings()?.clinic_name)

  getSettings() {
    return this.http.get(this.apiUrl);
  }

  saveSettings(payload: any) {
    return this.http.post(this.apiUrl, payload);
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