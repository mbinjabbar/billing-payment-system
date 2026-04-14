import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { catchError, throwError } from 'rxjs';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {

  const authService = inject(AuthService);
  const router = inject(Router);
  const token = authService.getToken();

  const cloned = token
    ? req.clone({ headers: req.headers.set('Authorization', `Bearer ${token}`) })
    : req;

  return next(cloned).pipe(
    catchError((error: HttpErrorResponse) => {

      if (error.status === 401) {
        authService.clearSession();
        router.navigate(['/login']);
      }

      if (error.status === 403) {
        router.navigate(['/forbidden']);
      }

      if (error.status === 0) {
        console.error('No connection to server');
      }

      return throwError(() => error);
    })
  );
};