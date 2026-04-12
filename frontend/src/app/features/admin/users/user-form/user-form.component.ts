import { Component, inject, signal, computed } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl, Validators } from '@angular/forms';
import { UserService } from '../../../../core/services/user.service';

@Component({
  selector: 'app-user-form',
  standalone: true,
  imports: [CommonModule, RouterLink, ReactiveFormsModule],
  templateUrl: './user-form.component.html',
})
export class UserFormComponent {
  private route       = inject(ActivatedRoute);
  private router      = inject(Router);
  private userService = inject(UserService);

  userId     = 0;
  submitting = signal(false);
  loading    = signal(false);
  error      = signal('');
  showPassword = false;

  isEdit = computed(() => this.userId > 0);

  userForm = new FormGroup({
    first_name: new FormControl('', Validators.required),
    last_name:  new FormControl('', Validators.required),
    email:      new FormControl('', [Validators.required, Validators.email]),
    password:   new FormControl('', [Validators.minLength(8)]),
    role:       new FormControl('Biller', Validators.required),
  });

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.userId = parseInt(id, 10);
      this.loadUser(this.userId);
      // Password optional on edit
      this.userForm.get('password')?.clearValidators();
      this.userForm.get('password')?.updateValueAndValidity();
    } else {
      // Password required on create
      this.userForm.get('password')?.setValidators([Validators.required, Validators.minLength(8)]);
      this.userForm.get('password')?.updateValueAndValidity();
    }
  }

  loadUser(id: number) {
    this.loading.set(true);
    this.userService.getUserById(id).subscribe({
      next: (res: any) => {
        const u = res.data ?? res;
        this.userForm.patchValue({
          first_name: u.first_name,
          last_name:  u.last_name,
          email:      u.email,
          role:       u.role,
        });
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Failed to load user.');
        this.loading.set(false);
      }
    });
  }

  onSubmit() {
    if (this.userForm.invalid) {
      this.userForm.markAllAsTouched();
      return;
    }

    this.submitting.set(true);
    this.error.set('');

    const payload: any = {
      first_name: this.userForm.get('first_name')?.value,
      last_name:  this.userForm.get('last_name')?.value,
      email:      this.userForm.get('email')?.value,
      role:       this.userForm.get('role')?.value,
    };

    // Only include password if provided
    const password = this.userForm.get('password')?.value;
    if (password) payload.password = password;

    if (this.isEdit()) {
      this.userService.updateUser(this.userId, payload).subscribe({
        next: () => this.router.navigate(['/admin/users']),
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to update user.');
          this.submitting.set(false);
        }
      });
    } else {
      this.userService.createUser(payload).subscribe({
        next: () => this.router.navigate(['/admin/users']),
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to create user.');
          this.submitting.set(false);
        }
      });
    }
  }

  togglePassword() { this.showPassword = !this.showPassword; }
}