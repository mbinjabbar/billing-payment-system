import { inject, Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Router } from '@angular/router';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: 'Admin' | 'Biller' | 'Payment Poster';
}

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = 'http://localhost:3000/api/auth';
  private http = inject(HttpClient);
  private router = inject(Router);

  login(email: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, { email, password });
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  setToken(token: string): void {
    localStorage.setItem('token', token);
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  clearSession(): void {
    localStorage.removeItem('token');
  }

  getUser(): AuthUser | null {
    const token = this.getToken();
    if (!token) return null;

    try {
      const payload = JSON.parse(atob(token.split('.')[1]));

      if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
        this.clearSession();
        return null;
      }

      return {
        id: payload.id,
        name: payload.name,
        email: payload.email,
        role: payload.role,
      };
    } catch {
      return null;
    }
  }

  getRole(): 'Admin' | 'Biller' | 'Payment Poster' | null {
    return this.getUser()?.role ?? null;
  }

  getUserId(): number | null {
    return this.getUser()?.id ?? null;
  }

  redirectByRole(): void {
    const role = this.getRole();
    switch (role) {
      case 'Admin':
        this.router.navigate(['/admin']);
        break;
      case 'Biller':
        this.router.navigate(['/biller']);
        break;
      case 'Payment Poster':
        this.router.navigate(['/payment-poster']);
        break;
      default:
        this.router.navigate(['/login']);
        break;
    }
  }
}
