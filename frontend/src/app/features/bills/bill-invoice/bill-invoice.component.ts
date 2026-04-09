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
  private route       = inject(ActivatedRoute);
  private billService = inject(BillService);

  bill    = signal<any>(null);
  loading = signal(true);
  error   = signal(false);

  // Computed helpers
  patientName = computed(() => {
    const b = this.bill();
    if (!b) return '—';
    const p = b?.visit?.appointment?.patient_case?.patient;
    return p ? `${p.first_name} ${p.last_name}` : '—';
  });

  patientPhone = computed(() => this.bill()?.visit?.appointment?.patient_case?.patient?.phone ?? '—');
  patientEmail = computed(() => this.bill()?.visit?.appointment?.patient_case?.patient?.email ?? '—');
  patientAddress = computed(() => this.bill()?.visit?.appointment?.patient_case?.patient?.address ?? '—');

  doctorName = computed(() => this.bill()?.visit?.appointment?.doctor_name ?? '—');
  caseNumber = computed(() => this.bill()?.visit?.appointment?.patient_case?.case_number ?? '—');
  caseType = computed(() => this.bill()?.visit?.appointment?.patient_case?.case_type ?? '—');
  caseCategory = computed(() => this.bill()?.visit?.appointment?.patient_case?.case_category ?? '—');


  insuranceName = computed(() => this.bill()?.insurance_firm?.name ?? 'No Insurance');
  insuranceCarrierCode = computed(() => this.bill()?.insurance_firm?.carrier_code ?? '-')
  insuranceFirmType = computed(() => this.bill()?.insurance_firm?.firm_type ?? '-')
  insuranceContactPerson = computed(() => this.bill()?.insurance_firm?.contact_person ?? '—');
  insurancePhone = computed(() => this.bill()?.insurance_firm?.phone ?? '—');
  insuranceEmail = computed(() => this.bill()?.insurance_firm?.email ?? '—');
  insuranceAddress = computed(() => this.bill()?.insurance_firm?.address ?? '—');

  procedureCodes = computed(() => this.bill()?.procedure_codes ?? []);

  getStatusClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'paid':      return 'bg-secondary-container text-on-secondary-container';
      case 'pending':   return 'bg-error-container/20 text-error';
      case 'draft':     return 'bg-primary-container text-on-primary-container';
      case 'submitted': return 'bg-tertiary-container text-on-tertiary-container';
      default:          return 'bg-surface-container-high text-on-surface-variant';
    }
  }

  downloadPdf(billId: number) {
    const url = `http://localhost:8000/api/bills/pdf/${billId}`;
    const link = document.createElement('a');
    link.href = url;
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
      }
    });
  }
}