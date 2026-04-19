import { Component, computed, inject } from '@angular/core';
import { Router, RouterOutlet, NavigationEnd } from '@angular/router';
import { SidebarComponent } from './shared/sidebar/sidebar.component';
import { HeaderComponent } from './shared/header/header.component';
import { CommonModule } from '@angular/common';
import { toSignal } from '@angular/core/rxjs-interop';
import { filter, map, startWith } from 'rxjs';
import { SettingsService } from './core/services/settings.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, SidebarComponent, HeaderComponent, CommonModule],
  templateUrl: './app.component.html',
})
export class AppComponent {
  private router = inject(Router);
  private settingsService = inject(SettingsService);

  private url = toSignal(
    this.router.events.pipe(
      filter(e => e instanceof NavigationEnd),
      map(e => (e as NavigationEnd).urlAfterRedirects),
      startWith(this.router.url)
    )
  );

  showLayout = computed(() => {
    const url = this.url();
    if (!url) return false;

    const hideLayoutRoutes = [
      '/login',
      '/not-found',
      '**'
    ];

    return !hideLayoutRoutes.some(r => url.includes(r));
  });

  ngOnInit() {
    this.settingsService.load();
  }
}