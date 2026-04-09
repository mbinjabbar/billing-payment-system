import { Component,inject } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [],
  templateUrl: './header.component.html',
})
export class HeaderComponent {
   private authService = inject(AuthService);
  private router = inject(Router);

  onlogout():void {
    this.authService.clearSession();
    this.router.navigate(['/login']);
  }

}