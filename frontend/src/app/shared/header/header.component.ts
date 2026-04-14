import { Component, computed, inject } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { SettingsService } from '../../core/services/settings.service';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './header.component.html',
})
export class HeaderComponent {
  private authService = inject(AuthService);
  private router      = inject(Router);
  settings = inject(SettingsService);

  user = computed(() => this.authService.getUser());

  onLogout(): void {
    this.authService.logout().subscribe({
      next:  () => this.clearAndRedirect(),
      error: () => this.clearAndRedirect(),
    });
  }

  private clearAndRedirect(): void {
    this.authService.clearSession();
    this.router.navigate(['/login']);
  }
}