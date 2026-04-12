import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { UserService } from '../../../../core/services/user.service';
import { AuthService } from '../../../../core/services/auth.service';

@Component({
  selector: 'app-user-list',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './user-list.component.html',
})
export class UserListComponent {
  private userService = inject(UserService);
  private authService = inject(AuthService);

  users           = signal<any[]>([]);
  loading         = signal(true);
  confirmDeleteId = signal<number | null>(null);
  error           = signal('');

  currentUserId = this.authService.getUserId();

  ngOnInit() {
    this.loadUsers();
  }

  loadUsers() {
    this.loading.set(true);
    this.userService.getUsers().subscribe({
      next: (res: any) => {
        this.users.set(res.data ?? res);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Failed to load users.');
        this.loading.set(false);
      }
    });
  }

  confirmDelete(id: number) { this.confirmDeleteId.set(id); }
  cancelDelete()            { this.confirmDeleteId.set(null); }

  executeDelete() {
    const id = this.confirmDeleteId();
    if (!id) return;
    this.userService.deleteUser(id).subscribe({
      next: () => {
        this.confirmDeleteId.set(null);
        this.loadUsers();
      },
      error: () => {
        this.error.set('Failed to delete user.');
        this.confirmDeleteId.set(null);
      }
    });
  }

  getRoleClass(role: string): string {
    switch (role) {
      case 'Admin':          return 'bg-purple-100 text-purple-700';
      case 'Biller':         return 'bg-cyan-100 text-cyan-700';
      case 'Payment Poster': return 'bg-orange-100 text-orange-700';
      default:               return 'bg-gray-100 text-gray-600';
    }
  }
}