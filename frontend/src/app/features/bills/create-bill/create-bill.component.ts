import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ProcedureCodesService } from '../../../core/services/procedure-codes.service';
import { VisitService } from '../../../core/services/visit.service';
import { InsuranceFirmsService } from '../../../core/services/insurance-firms.service';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-create-bill',
  imports: [FormsModule, RouterLink, CommonModule],
  templateUrl: './create-bill.component.html',
})
export class CreateBillComponent {
  private route                = inject(ActivatedRoute);
  private router                = inject(Router);
  private procedureCodesService = inject(ProcedureCodesService);
  private insuranceFirmsService = inject(InsuranceFirmsService);
  private visitService          = inject(VisitService);
  private billService           = inject(BillService);

  protected Number = Number;

  visit = signal<any>({ data: {} });

  procedures         = signal<any>({ data: [] });
  selectedProcedures = signal<any[]>([]);
  procedureDropdownOpen = false;
  procedureSearch    = '';

  insuranceFirms      = signal<any>({ data: [] });
  insuranceSearch     = signal('');
  selectedInsuranceId = signal<number | null>(null);
  insuranceDropdownOpen = false;

  dueDate = '';
  notes   = '';

  billing = signal({ insurance: 0, discount: 0, tax: 0 });

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('visitId');
    this.loadProcedureCodes();
    this.loadInsuranceFirms();
    this.loadVisitById(Number(id));
  }

  loadProcedureCodes() {
    this.procedureCodesService.getProcedureCodes().subscribe((res) => {
      this.procedures.set(res);
    });
  }

  loadInsuranceFirms() {
    this.insuranceFirmsService.getInsuranceFirms().subscribe((res) => {
      this.insuranceFirms.set(res);
    });
  }

  loadVisitById(id: number) {
    this.visitService.getVisitById(id).subscribe((res) => {
      this.visit.set(res);
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
    this.procedureSearch = '';
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
    const visit = this.visit();
    if (!visit?.data?.appointment?.patient_case) return [];

    const caseType = visit.data.appointment.patient_case.car_accident ? 'auto' : 'health';
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
    const billing = this.billing();
    const hasInsurance = !!this.selectedInsuranceId();

    const total = this.selectedProcedures().reduce(
      (sum, p) => sum + Number(p.standard_charge), 0
    );

    const insuranceAmount = hasInsurance ? (billing.insurance / 100) * total : 0;

    let remaining = total - insuranceAmount;
    remaining    -= Number(billing.discount);

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

  generateBill() {
    if (this.selectedProcedures().length === 0) return;

    const summary = this.summary();
    const payload = {
      visit_id:          this.visit().data?.id,
      insurance_firm_id: this.selectedInsuranceId(),
      created_by:        1,
      procedure_codes:   this.selectedProcedures().map((p) => ({ code: p.code, name: p.name, standard_charge: p.standard_charge})),
      charges:           Number(summary.total),
      insurance_coverage: Number(this.billing().insurance),
      discount_amount:   Number(this.billing().discount),
      tax_amount:        Number(this.billing().tax),
      bill_amount:       Number(summary.final),
      due_date:          this.dueDate || null,
      notes:             this.notes || null,
      status:            'Pending',
      paid_amount:       0,
    };

    this.billService.createBill(payload).subscribe({
      next: (res: any) => {
      console.log('Bill created:', res);
      const billId = res.data.id;
      this.router.navigate(['bills/invoice', billId]);
    },
      error: (err) => console.error('Error:', err),
    });
  }

  saveDraft() {
    console.log('Saved as draft');
  }
}