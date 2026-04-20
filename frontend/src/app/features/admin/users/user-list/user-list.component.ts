import { Component, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { UserService } from '../../../../core/services/user.service';
import { AuthService } from '../../../../core/services/auth.service';
import { debounceTime, distinctUntilChanged, Subject } from 'rxjs';

@Component({
  selector: 'app-user-list',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './user-list.component.html',
})
export class UserListComponent {
  private userService = inject(UserService);
  private authService = inject(AuthService);

  // state
  users = signal<any[]>([]);
  loading = signal(true);
  error = signal('');
  confirmDeleteId = signal<number | null>(null);

  currentUserId = this.authService.getUserId();

  searchTerm = signal('');
  private searchSubject = new Subject<string>();

  // pagination
  currentPage = signal(1);
  totalPages = signal(1);
  totalItems = signal(0);
  perPage = signal(10);

  from = computed(() =>
    (this.currentPage() - 1) * this.perPage() + 1
  );

  to = computed(() =>
    Math.min(this.currentPage() * this.perPage(), this.totalItems())
  );

  visiblePages = computed(() => {
    const total = this.totalPages();
    const current = this.currentPage();
    const pages: (number | string)[] = [];

    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
      return pages;
    }

    pages.push(1);

    if (current > 3) pages.push('...');

    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i);
    }

    if (current < total - 2) pages.push('...');

    pages.push(total);

    return pages;
  });

  ngOnInit() {
    this.loadUsers(1);
    this.searchSubject.pipe(
      debounceTime(400),
      distinctUntilChanged()
    ).subscribe(searchValue => {
      this.searchTerm.set(searchValue);
      this.currentPage.set(1);
      this.loadUsers(1);
    });
  }

  onSearch(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  this.searchSubject.next(value);
}

 loadUsers(page: number) {
  this.loading.set(true);

  this.userService.getUsers(page, this.perPage(), this.searchTerm()).subscribe({
    next: (res: any) => {
      this.users.set(res.data?.data ?? res.data ?? []);
      const meta = res.data?.meta;
      if (meta) {
        this.currentPage.set(meta.current_page);
        this.totalPages.set(meta.last_page);
        this.totalItems.set(meta.total);
        this.perPage.set(meta.per_page);
      }
      this.loading.set(false);
    },
    error: () => {
      this.error.set('Failed to load users.');
      this.loading.set(false);
    },
  });
}

  // pagination action
  goToPage(page: number) {
    if (page < 1 || page > this.totalPages()) return;
    this.loadUsers(page);
  }

  // delete flow
  confirmDelete(id: number) {
    this.confirmDeleteId.set(id);
  }

  cancelDelete() {
    this.confirmDeleteId.set(null);
  }

  executeDelete() {
    const id = this.confirmDeleteId();
    if (!id) return;

    this.userService.deleteUser(id).subscribe({
      next: () => {
        this.confirmDeleteId.set(null);
        this.loadUsers(this.currentPage());
      },
      error: () => {
        this.error.set('Failed to delete user.');
        this.confirmDeleteId.set(null);
      },
    });
  }

  // UI helper
  getRoleClass(role: string): string {
    switch (role) {
      case 'Admin':
        return 'bg-purple-100 text-purple-700';
      case 'Biller':
        return 'bg-cyan-100 text-cyan-700';
      case 'Payment Poster':
        return 'bg-orange-100 text-orange-700';
      default:
        return 'bg-gray-100 text-gray-600';
    }
  }
}