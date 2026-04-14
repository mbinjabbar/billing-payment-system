import { Component, inject, signal, computed } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { BillService } from '../../../core/services/bill.service';

@Component({
  selector: 'app-bill-invoice',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './bill-invoice.component.html',
})
export class BillInvoiceComponent {
  private route = inject(ActivatedRoute);
  private billService = inject(BillService);

  bill = signal<any>(null);
  loading = signal(true);
  error = signal(false);

  patientName = computed(() => {
    const b = this.bill();
    if (!b) return '—';
    const p = b?.visit?.appointment?.patient_case?.patient;
    return p ? `${p.first_name} ${p.last_name}` : '—';
  });

  patientPhone = computed(
    () => this.bill()?.visit?.appointment?.patient_case?.patient?.phone ?? '—',
  );
  patientEmail = computed(
    () => this.bill()?.visit?.appointment?.patient_case?.patient?.email ?? '—',
  );
  patientAddress = computed(
    () =>
      this.bill()?.visit?.appointment?.patient_case?.patient?.address ?? '—',
  );

  doctorName = computed(
    () => this.bill()?.visit?.appointment?.doctor_name ?? '—',
  );
  caseNumber = computed(
    () => this.bill()?.visit?.appointment?.patient_case?.case_number ?? '—',
  );
  caseType = computed(
    () => this.bill()?.visit?.appointment?.patient_case?.case_type ?? '—',
  );
  caseCategory = computed(
    () => this.bill()?.visit?.appointment?.patient_case?.case_category ?? '—',
  );

  insuranceName = computed(
    () => this.bill()?.insurance_firm?.name ?? 'No Insurance',
  );
  insuranceCarrierCode = computed(
    () => this.bill()?.insurance_firm?.carrier_code ?? '-',
  );
  insuranceFirmType = computed(
    () => this.bill()?.insurance_firm?.firm_type ?? '-',
  );
  insuranceContactPerson = computed(
    () => this.bill()?.insurance_firm?.contact_person ?? '—',
  );
  insurancePhone = computed(() => this.bill()?.insurance_firm?.phone ?? '—');
  insuranceEmail = computed(() => this.bill()?.insurance_firm?.email ?? '—');
  insuranceAddress = computed(
    () => this.bill()?.insurance_firm?.address ?? '—',
  );

  procedureCodes = computed(() => this.bill()?.procedure_codes ?? []);

  getStatusClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'paid':
        return 'bg-green-200 text-green-800';
      case 'pending':
        return 'bg-orange-200 text-orange-800';
      case 'draft':
        return 'bg-purple-200 text-purple-800';
      default:
        return 'bg-surface-container-high text-on-surface-variant';
    }
  }

  isCarAccident = computed(
    () => this.bill()?.visit?.appointment?.patient_case?.car_accident ?? false,
  );

  downloadInvoice(billId: number) {
    const link = document.createElement('a');
    link.href = `http://localhost:8000/api/bills/invoice/${billId}`;
    link.click();
  }

  downloadNF2(billId: number) {
    const link = document.createElement('a');
    link.href = `http://localhost:8000/api/bills/nf2/${billId}`;
    link.click();
  }

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('billId');
    this.billService.getBillById(Number(id)).subscribe({
      next: (res: any) => {
        this.bill.set(res.data ?? res);
        this.loading.set(false);
      },
      error: () => {
        this.error.set(true);
        this.loading.set(false);
      },
    });
  }
}
