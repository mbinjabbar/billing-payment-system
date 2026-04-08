import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { ProcedureCodesService } from '../../../core/services/procedure-codes.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-create-bill',
  imports: [FormsModule, RouterLink, CommonModule],
  templateUrl: './create-bill.component.html',
  styleUrl: './create-bill.component.css'
})
export class CreateBillComponent {
  private procedureCodesSerivce = inject(ProcedureCodesService);

  visit = signal({
    patient_name: 'Sarah Mitchell',
    date: 'Oct 24, 2023'
  });

  procedures = signal<any>({ data: [] });
  selectedProcedures = signal<any[]>([]);
  dropdownOpen = false;
procedureSearch = '';

  ngOnInit(){
    this.loadProcedureCodes()
  }

  loadProcedureCodes(){
    this.procedureCodesSerivce.getProcedureCodes().subscribe((procedureCodes)=> {
      this.procedures.set(procedureCodes)
      console.log(procedureCodes)
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
    const payload = {
      visit: this.visit(),
      procedures: this.procedures(),
      billing: this.billing(),
      summary: this.summary()
    };

    console.log('Generate Bill Payload:', payload);
  }

  saveDraft() {
    console.log('Saved as draft');
  }

addProcedure(code: string) {
  const selected = this.procedures().data.find((p:any) => p.code === code);
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
  return this.procedures().data.filter((p:any) =>
    p.code.toLowerCase().includes(search) ||
    p.name.toLowerCase().includes(search)
  );
}
}