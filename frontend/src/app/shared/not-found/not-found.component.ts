import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { SettingsService } from '../../core/services/settings.service';

@Component({
  selector: 'app-not-found',
  templateUrl: './not-found.component.html',
})
export class NotFoundComponent {

  private router = inject(Router);
  private authService = inject(AuthService);
  settings = inject(SettingsService);

  goBack() {
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate(['/login']);
    }
  }

  getFallbackText() {
    return this.authService.isLoggedIn()
      ? 'Back to Dashboard'
      : 'Go to Login';
  }
}