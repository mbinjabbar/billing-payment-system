import { CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { inject } from '@angular/core';

export const roleGuard = (allowedRoles: string[]): CanActivateFn => {
  return () => {
    const authService = inject(AuthService);

    const role = authService.getRole();

    if (role && allowedRoles.includes(role)) {
      return true;
    }

    authService.redirectByRole();
    return false;
  };
};
