import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ProcedureCodesService } from '../../../core/services/procedure-codes.service';
import { CommonModule } from '@angular/common';
import { VisitService } from '../../../core/services/visit.service';
import { InsuranceFirmsService } from '../../../core/services/insurance-firms.service';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-create-bill',
  imports: [FormsModule, RouterLink, CommonModule],
  templateUrl: './create-bill.component.html',
  styleUrl: './create-bill.component.css'
})
export class CreateBillComponent {
  private route = inject(ActivatedRoute);
  private procedureCodesSerivce = inject(ProcedureCodesService);
  private insuranceFirmsService = inject(InsuranceFirmsService);
  private visitService = inject(VisitService);
  private billService = inject(BillService);

  visit = signal<any>({ data: [] });

  procedures = signal<any>({ data: [] });
  selectedProcedures = signal<any[]>([]);
  dropdownOpen = false;
  procedureSearch = '';

  insuranceFirms = signal<any>({ data: [] });
  insuranceSearch = signal('');
  selectedInsuranceId: number | null = null;


  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('visitId');
    this.loadProcedureCodes()
    this.loadInsuranceFirms()
    this.loadVisitById(Number(id))
  }

  loadProcedureCodes() {
    this.procedureCodesSerivce.getProcedureCodes().subscribe((procedureCodes) => {
      this.procedures.set(procedureCodes)
      console.log(procedureCodes)
    })
  }

  loadInsuranceFirms() {
    this.insuranceFirmsService.getInsuranceFirms().subscribe((insuranceFirms) => {
      this.insuranceFirms.set(insuranceFirms);
    })
  }

  loadVisitById(visitId: number) {
    return this.visitService.getVisitById(visitId).subscribe((visit) => {
      this.visit.set(visit)
      console.log(visit)
    })
  }

  billing = signal({
    insurance: 80,
    discount: 0,
    tax: 0
  });

  summary = computed(() => {

    const billing = this.billing();

    const total = this.selectedProcedures().reduce((sum, p) => {
      return sum + Number(p.standard_charge);
    }, 0);

    const insuranceAmount = (billing.insurance / 100) * total;

    let remaining = total - insuranceAmount;

    remaining -= billing.discount;

    const taxAmount = (billing.tax / 100) * remaining;

    const final = remaining + taxAmount;

    return {
      total: total.toFixed(2),
      insurance: insuranceAmount.toFixed(2),
      final: final.toFixed(2),
      discount: billing.discount.toFixed(2),
      tax: taxAmount.toFixed(2),
    };
  });

generateBill() {
  const summary = this.summary();

  const payload = {
    visit_id: this.visit().data.id,
    insurance_firm_id: this.selectedInsuranceId,
    created_by:  1,
    procedure_codes: this.selectedProcedures().map(p => p.code),
    charges: Number(summary.total),
    insurance_coverage: Number(this.billing().insurance),
    discount_amount: Number(this.billing().discount),
    tax_amount: Number(this.billing().tax),
    bill_amount: Number(summary.final),
    due_date: null,
    status: 'Pending',
    paid_amount: 0,
    notes: null
  };

  this.billService.createBill(payload).subscribe({
    next: res => console.log(res),
    error: err => console.error(err)
  });
}

  saveDraft() {
    console.log('Saved as draft');
  }

  addProcedure(code: string) {
    const selected = this.procedures().data.find((p: any) => p.code === code);
    if (!selected) return;

    const exists = this.selectedProcedures().some(p => p.code === code);
    if (exists) return;

    this.selectedProcedures.update(list => [...list, selected]);
  }

  removeProcedure(index: number) {
    this.selectedProcedures.update(list =>
      list.filter((_, i) => i !== index)
    );
  }

  updateBilling(field: string, value: number) {
    let val = Number(value);

    if (field === 'insurance' || field === 'tax') {
      val = Math.max(0, Math.min(100, val));
    }

    this.billing.update(b => ({
      ...b,
      [field]: val
    }));
  }

  get selectedProceduresText(): string {
    const selected = this.selectedProcedures();
    return selected.length ? selected.map(p => p.code).join(', ') : 'Select Procedure';
  }

  get filteredProcedures(): any[] {
    const search = this.procedureSearch.toLowerCase();
    return this.procedures().data.filter((p: any) =>
      p.code.toLowerCase().includes(search) ||
      p.name.toLowerCase().includes(search)
    );
  }

get filteredInsuranceFirms() {
  const visit = this.visit();

  if (!visit?.data?.appointment?.patient_case) return [];

  const caseType = visit.data.appointment.patient_case.car_accident
    ? 'auto'
    : 'health';

  return this.insuranceFirms().data.filter(
    (firm: any) => firm.firm_type.toLowerCase() === caseType
  );
}

  selectInsurance(id: number) {
  this.selectedInsuranceId = id;
}
}