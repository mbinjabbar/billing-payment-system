import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const loginGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const user = authService.getUser();
  const router = inject(Router);

  if (authService.isLoggedIn() && user) {
    if (user?.role === 'Admin') {
      return router.createUrlTree(['/admin']);
    }
    if (user?.role === 'Biller') {
      return router.createUrlTree(['/biller']);
    }
    if (user?.role === 'Payment Poster') {
      return router.createUrlTree(['/payment-poster']);
    }
  }

  return true;
};
