import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { SettingsService } from '../../core/services/settings.service';

@Component({
  selector: 'app-forbidden',
  templateUrl: './forbidden.component.html',
})
export class ForbiddenComponent {

  private router = inject(Router);
  private auth = inject(AuthService);
  settings = inject(SettingsService);

  goBack() {
    if (this.auth.isLoggedIn()) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate(['/login']);
    }
  }

  getRedirectText() {
    return this.auth.isLoggedIn()
      ? 'Back to Dashboard'
      : 'Go to Login';
  }
}