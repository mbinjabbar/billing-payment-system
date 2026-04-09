import { Component,inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../../core/services/auth.service';
import { Router} from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  private authService = inject(AuthService);
  private router = inject(Router);
  loginForm: FormGroup;
  showPassword = false;
  isLoading = false;
  loginError = '';

  constructor(private fb: FormBuilder) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
    });
  }

  get email() {
    return this.loginForm.get('email')!;
  }

  get password() {
    return this.loginForm.get('password')!;
  }

  togglePassword() {
    this.showPassword = !this.showPassword;
  }

  onSubmit() {
    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }
    const { email, password} = this.loginForm.value;
    this.authService.login(email, password).subscribe({
      next: (res) => {
        this.authService.setToken(res.data.token);
        this.router.navigate(['/biller']);
      },
      error: (err) => {
        this.loginError = err.error?.message || 'Invalid email or password';
        setTimeout(() => (this.loginError = ''), 2500);
      },
    });
    this.isLoading = true;
    setTimeout(() => {
      this.isLoading = false;
      console.log('Form submitted:', this.loginForm.value);
    }, 2000);
  }

   
}