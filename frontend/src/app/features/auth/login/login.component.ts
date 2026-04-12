import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  private authService = inject(AuthService);
  loginForm: FormGroup;
  showPassword = false;
  isLoading    = false;
  loginError   = '';

  constructor(private fb: FormBuilder) {
    this.loginForm = this.fb.group({
      email:    ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
    });
  }

  get email()    { return this.loginForm.get('email')!; }
  get password() { return this.loginForm.get('password')!; }

  togglePassword() { this.showPassword = !this.showPassword; }

  onSubmit() {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    this.isLoading = true;
    const { email, password } = this.loginForm.value;

    this.authService.login(email, password).subscribe({
      next: (res) => {
        this.authService.setToken(res.data.token);
        this.authService.redirectByRole();
      },
      error: (err) => {
        this.isLoading  = false;
        this.loginError = err.error?.message || 'Invalid email or password';
        setTimeout(() => (this.loginError = ''), 2500);
      },
    });
  }
}