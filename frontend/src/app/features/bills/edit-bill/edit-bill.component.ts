import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ProcedureCodesService } from '../../../core/services/procedure-codes.service';
import { InsuranceFirmsService } from '../../../core/services/insurance-firms.service';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-edit-bill',
  standalone: true,
  imports: [FormsModule, RouterLink, CommonModule],
  templateUrl: './edit-bill.component.html',
})
export class EditBillComponent {
  private route                 = inject(ActivatedRoute);
  private router                = inject(Router);
  private procedureCodesService = inject(ProcedureCodesService);
  private insuranceFirmsService = inject(InsuranceFirmsService);
  private billService           = inject(BillService);

  protected Number = Number;

  bill    = signal<any>(null);
  loading = signal(true);
  saving  = signal(false);
  error   = signal('');

  procedures            = signal<any>({ data: [] });
  selectedProcedures    = signal<any[]>([]);
  procedureDropdownOpen = false;
  procedureSearch       = '';

  insuranceFirms        = signal<any>({ data: [] });
  insuranceSearch       = signal('');
  selectedInsuranceId   = signal<number | null>(null);
  insuranceDropdownOpen = false;

  dueDate = '';
  notes   = '';
  billing = signal({ insurance: 0, discount: 0, tax: 0 });

  ngOnInit() {
    const id = Number(this.route.snapshot.paramMap.get('billId'));

    this.procedureCodesService.getProcedureCodes(true).subscribe((res: any) => {
      this.procedures.set(res);
    });

    this.insuranceFirmsService.getInsuranceFirms(true).subscribe((res: any) => {
      this.insuranceFirms.set(res);
    });

    this.billService.getBillById(id).subscribe({
      next: (res: any) => {
        const b = res.data ?? res;
        this.bill.set(b);

        this.selectedProcedures.set(b.procedure_codes ?? []);
        this.selectedInsuranceId.set(b.insurance_firm_id ?? null);
        this.dueDate = b.due_date ?? '';
        this.notes   = b.notes   ?? '';

        this.billing.set({
          insurance: Number(b.insurance_coverage),
          discount:  Number(b.discount_amount),
          tax:       Number(b.tax_amount),
        });

        this.loading.set(false);
      },
      error: () => {
        this.error.set('Failed to load bill. Please go back and try again.');
        this.loading.set(false);
      }
    });
  }

  toggleProcedureDropdown() {
    this.procedureDropdownOpen = !this.procedureDropdownOpen;
    if (this.insuranceDropdownOpen) this.insuranceDropdownOpen = false;
  }

  get filteredProcedures(): any[] {
    const search = this.procedureSearch.toLowerCase();
    return this.procedures().data.filter((p: any) =>
      p.code.toLowerCase().includes(search) ||
      p.name.toLowerCase().includes(search)
    );
  }

  isProcedureSelected(code: string): boolean {
    return this.selectedProcedures().some((p) => p.code === code);
  }

  addProcedure(code: string) {
    const found = this.procedures().data.find((p: any) => p.code === code);
    if (!found || this.isProcedureSelected(code)) return;
    this.selectedProcedures.update((list) => [...list, found]);
    this.procedureSearch       = '';
    this.procedureDropdownOpen = false;
  }

  removeProcedure(index: number) {
    this.selectedProcedures.update((list) => list.filter((_, i) => i !== index));
  }

  toggleInsuranceDropdown() {
    this.insuranceDropdownOpen = !this.insuranceDropdownOpen;
    if (this.procedureDropdownOpen) this.procedureDropdownOpen = false;
  }

  get filteredInsuranceFirms(): any[] {
    const b = this.bill();
    if (!b?.visit?.appointment?.patient_case) return [];

    const caseType = b.visit.appointment.patient_case.car_accident ? 'auto' : 'health';
    const search   = this.insuranceSearch().toLowerCase();

    return this.insuranceFirms().data.filter((firm: any) =>
      firm.firm_type?.toLowerCase() === caseType &&
      (firm.name?.toLowerCase().includes(search) ||
       firm.firm_type?.toLowerCase().includes(search))
    );
  }

  selectInsurance(id: number) {
    if (this.selectedInsuranceId() === id) {
      this.selectedInsuranceId.set(null);
      this.billing.update((b) => ({ ...b, insurance: 0 }));
    } else {
      this.selectedInsuranceId.set(id);
    }
    this.insuranceDropdownOpen = false;
    this.insuranceSearch.set('');
  }

  getSelectedInsuranceName(): string {
    const firm = this.insuranceFirms().data.find(
      (f: any) => f.id === this.selectedInsuranceId()
    );
    return firm?.name ?? '';
  }

  getSelectedInsuranceType(): string {
    const firm = this.insuranceFirms().data.find(
      (f: any) => f.id === this.selectedInsuranceId()
    );
    return firm ? `${firm.firm_type} insurance` : '';
  }

  updateBilling(field: string, value: number) {
    if (field === 'insurance' && !this.selectedInsuranceId()) return;

    let val = Number(value);
    if (field === 'insurance' || field === 'tax') {
      val = Math.max(0, Math.min(100, val));
    }
    this.billing.update((b) => ({ ...b, [field]: val }));
  }

  summary = computed(() => {
    const billing      = this.billing();
    const hasInsurance = !!this.selectedInsuranceId();

    const total = this.selectedProcedures().reduce(
      (sum, p) => sum + Number(p.standard_charge), 0
    );

    const insuranceAmount = hasInsurance ? (billing.insurance / 100) * total : 0;

    let remaining  = total - insuranceAmount;
    remaining     -= Number(billing.discount);

    const taxAmount = (billing.tax / 100) * remaining;
    const final     = remaining + taxAmount;

    return {
      total:     total.toFixed(2),
      insurance: insuranceAmount.toFixed(2),
      discount:  Number(billing.discount).toFixed(2),
      tax:       taxAmount.toFixed(2),
      final:     Math.max(0, final).toFixed(2),
    };
  });

  save() {
    if (this.selectedProcedures().length === 0) {
      this.error.set('At least one procedure code is required.');
      return;
    }

    this.saving.set(true);
    this.error.set('');

    const s = this.summary();


    const payload = {
      insurance_firm_id:  this.selectedInsuranceId(),
      procedure_codes:    this.selectedProcedures().map((p) => ({
        code: p.code, name: p.name, standard_charge: p.standard_charge
      })),
      charges:            Number(s.total),
      insurance_coverage: Number(this.billing().insurance),
      discount_amount:    Number(this.billing().discount),
      tax_amount:         Number(this.billing().tax),
      bill_amount:        Number(s.final),
      due_date:           this.dueDate || null,
      notes:              this.notes   || null,
    };

    this.billService.updateBill(this.bill().id, payload).subscribe({
      next: () => {
        this.router.navigate(['/bills/invoice', this.bill().id]);
      },
      error: () => {
        this.error.set('Failed to save changes. Please try again.');
        this.saving.set(false);
      }
    });
  }
}