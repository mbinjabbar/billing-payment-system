import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl, Validators } from '@angular/forms';
import { SettingsService } from '../../../../core/services/settings.service';

@Component({
  selector: 'app-config',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './app-config.component.html',
})
export class AppConfigComponent {
  private settingsService = inject(SettingsService);
  private settingsStore = inject(SettingsService);

  // UI State
  error = signal('');
  success = signal('');
  savingConfig = signal(false);

  // Form
  settingsForm = new FormGroup({
    clinic_name: new FormControl('', Validators.required),
    clinic_address: new FormControl(''),
    clinic_phone: new FormControl(''),
    clinic_email: new FormControl('', Validators.email),
    default_tax_rate: new FormControl('0'),
    default_due_days: new FormControl('30'),
    invoice_footer: new FormControl(''),
  });

  ngOnInit() {
    this.loadSettings();
  }

  loadSettings() {
    this.settingsService.getSettings().subscribe({
      next: (res: any) =>
        this.settingsForm.patchValue(res.data ?? res),

      error: () =>
        this.error.set('Failed to load settings.'),
    });
  }

  saveConfig() {
    if (this.settingsForm.invalid) {
      this.settingsForm.markAllAsTouched();
      return;
    }

    this.savingConfig.set(true);
    this.error.set('');
    this.success.set('');

    this.settingsService.saveSettings(this.settingsForm.value).subscribe({
      next: () => {
        this.success.set('Settings saved successfully.');

        // refresh global app settings after save so changes reflect immediately across the app
        this.settingsStore.load();

        this.savingConfig.set(false);
      },

      error: () => {
        this.error.set('Failed to save settings.');
        this.savingConfig.set(false);
      },
    });
  }
}