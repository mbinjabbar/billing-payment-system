import { Component, computed, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';

interface Procedure {
  code: string;
  name: string;
  description: string;
  units: number;
  charge: number;
}

@Component({
  selector: 'app-create-bill',
  imports: [FormsModule, RouterLink],
  templateUrl: './create-bill.component.html',
  styleUrl: './create-bill.component.css'
})
export class CreateBillComponent {

  visit = signal({
    patient_name: 'Sarah Mitchell',
    date: 'Oct 24, 2023'
  });

  procedures = signal<Procedure[]>([
    {
      code: '99214',
      name: 'Office Visit (Level 4)',
      description: 'Outpatient medical visit',
      units: 1,
      charge: 185
    },
    {
      code: '80053',
      name: 'Comprehensive Metabolic Panel',
      description: 'Laboratory screening',
      units: 1,
      charge: 72
    },
    {
      code: '90658',
      name: 'Flu Vaccine',
      description: 'Immunization service',
      units: 1,
      charge: 45
    }
  ]);

  billing = signal({
    insurance: 80,
    discount: 0,
    tax: 0
  });

  summary = computed(() => {

    const procedures = this.procedures();
    const billing = this.billing();

    const total = procedures.reduce((sum, p) => {
      return sum + (p.units * p.charge);
    }, 0);

    const insuranceAmount = (billing.insurance / 100) * total;

    let remaining = total - insuranceAmount;

    remaining -= billing.discount;

    const taxAmount = (billing.tax / 100) * remaining;

    const final = remaining + taxAmount;

    return {
      total: total.toFixed(2),
      insurance: insuranceAmount.toFixed(2),
      final: final.toFixed(2)
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

  updateUnits(index: number, value: number) {
    const list = [...this.procedures()];
    list[index].units = value;
    this.procedures.set(list);
  }

  addProcedure() {
    this.procedures.update(p => [
      ...p,
      {
        code: '00000',
        name: 'New Procedure',
        description: '',
        units: 1,
        charge: 0
      }
    ]);
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
}