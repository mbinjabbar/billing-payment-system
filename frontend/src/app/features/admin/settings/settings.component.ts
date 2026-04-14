import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl, Validators } from '@angular/forms';
import { ProcedureCodesService } from '../../../core/services/procedure-codes.service';
import { InsuranceFirmsService } from '../../../core/services/insurance-firms.service';
import { SettingsService } from '../../../core/services/settings.service';

type Tab = 'procedures' | 'insurance' | 'config';

@Component({
  selector: 'app-settings',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './settings.component.html',
})
export class SettingsComponent {
  private procedureService = inject(ProcedureCodesService);
  private insuranceService = inject(InsuranceFirmsService);
  private settingsService  = inject(SettingsService);

  activeTab = signal<Tab>('procedures');

  // ── Shared ───────────────────────────────────────────────────────────────
  loading           = signal(false);
  confirmDeleteId   = signal<number | null>(null);
  confirmDeleteType = signal<'procedure' | 'insurance' | null>(null);
  error             = signal('');
  success           = signal('');

  // ── Procedure Codes ───────────────────────────────────────────────────────
  procedures       = signal<any[]>([]);
  editingProcedure = signal<any>(null);

  procedureForm = new FormGroup({
    code:            new FormControl('', Validators.required),
    name:            new FormControl('', Validators.required),
    standard_charge: new FormControl('', [Validators.required, Validators.min(0)]),
    is_active:       new FormControl(true),
  });

  // ── Insurance Firms ───────────────────────────────────────────────────────
  insuranceFirms   = signal<any[]>([]);
  editingInsurance = signal<any>(null);

  insuranceForm = new FormGroup({
    name:           new FormControl('', Validators.required),
    firm_type:      new FormControl('Health', Validators.required),
    carrier_code:   new FormControl(''),
    contact_person: new FormControl(''),
    email:          new FormControl('', Validators.email),
    phone:          new FormControl(''),
    address:        new FormControl(''),
    is_active:      new FormControl(true),
  });

  // ── App Settings ─────────────────────────────────────────────────────────
  settingsForm = new FormGroup({
    clinic_name:      new FormControl('', Validators.required),
    clinic_address:   new FormControl(''),
    clinic_phone:     new FormControl(''),
    clinic_email:     new FormControl('', Validators.email),
    default_tax_rate: new FormControl('0'),
    default_due_days: new FormControl('30'),
    invoice_footer:   new FormControl(''),
  });

  savingConfig = signal(false);

  // ── Lifecycle ────────────────────────────────────────────────────────────
  ngOnInit() {
    this.loadProcedures();
    this.loadInsuranceFirms();
    this.loadSettings();
  }

  setTab(tab: Tab) {
    this.activeTab.set(tab);
    this.clearMessages();
    this.cancelProcedureEdit();
    this.cancelInsuranceEdit();
  }

  // ── Procedures ────────────────────────────────────────────────────────────
  loadProcedures() {
    this.procedureService.getProcedureCodes().subscribe({
      next: (res: any) => this.procedures.set(res.data ?? res),
      error: () => this.error.set('Failed to load procedure codes.'),
    });
  }

  editProcedure(p: any) {
    this.editingProcedure.set(p);
    this.procedureForm.patchValue({
      code:            p.code,
      name:            p.name,
      standard_charge: p.standard_charge,
      is_active:       p.is_active,
    });
    this.clearMessages();
  }

  cancelProcedureEdit() {
    this.editingProcedure.set(null);
    this.procedureForm.reset({ is_active: true });
  }

  saveProcedure() {
    if (this.procedureForm.invalid) {
      this.procedureForm.markAllAsTouched();
      return;
    }
    this.loading.set(true);
    this.clearMessages();

    const payload = this.procedureForm.value;
    const editing = this.editingProcedure();

    const req = editing
      ? this.procedureService.updateProcedureCode(editing.id, payload)
      : this.procedureService.createProcedureCode(payload);

    req.subscribe({
      next: () => {
        this.success.set(editing ? 'Procedure code updated.' : 'Procedure code created.');
        this.cancelProcedureEdit();
        this.loadProcedures();
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message || 'Failed to save procedure code.');
        this.loading.set(false);
      }
    });
  }

  // ── Insurance Firms ───────────────────────────────────────────────────────
  loadInsuranceFirms() {
    this.insuranceService.getInsuranceFirms().subscribe({
      next: (res: any) => this.insuranceFirms.set(res.data ?? res),
      error: () => this.error.set('Failed to load insurance firms.'),
    });
  }

  editInsurance(f: any) {
    this.editingInsurance.set(f);
    this.insuranceForm.patchValue(f);
    this.clearMessages();
  }

  cancelInsuranceEdit() {
    this.editingInsurance.set(null);
    this.insuranceForm.reset({ firm_type: 'Health', is_active: true });
  }

  saveInsurance() {
    if (this.insuranceForm.invalid) {
      this.insuranceForm.markAllAsTouched();
      return;
    }
    this.loading.set(true);
    this.clearMessages();

    const payload = this.insuranceForm.value;
    const editing = this.editingInsurance();

    const req = editing
      ? this.insuranceService.updateInsuranceFirm(editing.id, payload)
      : this.insuranceService.createInsuranceFirm(payload);

    req.subscribe({
      next: () => {
        this.success.set(editing ? 'Insurance firm updated.' : 'Insurance firm created.');
        this.cancelInsuranceEdit();
        this.loadInsuranceFirms();
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message || 'Failed to save insurance firm.');
        this.loading.set(false);
      }
    });
  }

  // ── App Settings ──────────────────────────────────────────────────────────
  loadSettings() {
    this.settingsService.getSettings().subscribe({
      next: (res: any) => {
        this.settingsForm.patchValue(res.data ?? res);
      },
      error: () => this.error.set('Failed to load settings.'),
    });
  }

  saveConfig() {
    if (this.settingsForm.invalid) {
      this.settingsForm.markAllAsTouched();
      return;
    }
    this.savingConfig.set(true);
    this.clearMessages();

    this.settingsService.saveSettings(this.settingsForm.value).subscribe({
      next: () => {
        this.success.set('Settings saved successfully.');
        this.savingConfig.set(false);
      },
      error: () => {
        this.error.set('Failed to save settings.');
        this.savingConfig.set(false);
      }
    });
  }

  // ── Delete ────────────────────────────────────────────────────────────────
  confirmDelete(id: number, type: 'procedure' | 'insurance') {
    this.confirmDeleteId.set(id);
    this.confirmDeleteType.set(type);
  }

  cancelDelete() {
    this.confirmDeleteId.set(null);
    this.confirmDeleteType.set(null);
  }

  executeDelete() {
    const id   = this.confirmDeleteId();
    const type = this.confirmDeleteType();
    if (!id || !type) return;

    const req = type === 'procedure'
      ? this.procedureService.deleteProcedureCode(id)
      : this.insuranceService.deleteInsuranceFirm(id);

    req.subscribe({
      next: () => {
        this.cancelDelete();
        this.success.set('Deleted successfully.');
        type === 'procedure' ? this.loadProcedures() : this.loadInsuranceFirms();
      },
      error: () => {
        this.error.set('Failed to delete. It may be in use by existing bills.');
        this.cancelDelete();
      }
    });
  }

  // ── Helpers ───────────────────────────────────────────────────────────────
  clearMessages() {
    this.error.set('');
    this.success.set('');
  }

  getFirmTypeClass(type: string): string {
    return type === 'Auto'
      ? 'bg-orange-100 text-orange-700'
      : 'bg-cyan-100 text-cyan-700';
  }
}