import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, ActivatedRoute } from '@angular/router';
import { PatientService } from '../../../core/services/patient.service';

@Component({
  selector: 'app-patient-detail',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './patient-detail.component.html',
})
export class PatientDetailComponent {
  private route          = inject(ActivatedRoute);
  private patientService = inject(PatientService);

  patient = signal<any>(null);
  loading = signal(true);
  error   = signal('');

  ngOnInit() {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.patientService.getPatientById(id).subscribe({
      next: (res: any) => {
        this.patient.set(res.data ?? res);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Failed to load patient details.');
        this.loading.set(false);
      }
    });
  }

  calculateAge(dob: string): number | string {
    if (!dob) return '—';
    const diff = Date.now() - new Date(dob).getTime();
    return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
  }

  getGenderClass(gender: string): string {
    switch (gender?.toLowerCase()) {
      case 'male':   return 'bg-blue-100 text-blue-700';
      case 'female': return 'bg-pink-100 text-pink-700';
      default:       return 'bg-gray-100 text-gray-600';
    }
  }

  getCaseStatusClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'active':      return 'bg-green-100 text-green-700';
      case 'closed':      return 'bg-gray-200 text-gray-600';
      case 'on hold':     return 'bg-orange-100 text-orange-700';
      case 'transferred': return 'bg-blue-100 text-blue-700';
      default:            return 'bg-gray-100 text-gray-700';
    }
  }

  getPriorityClass(priority: string): string {
    switch (priority?.toLowerCase()) {
      case 'urgent': return 'bg-red-100 text-red-700';
      case 'high':   return 'bg-orange-100 text-orange-700';
      case 'normal': return 'bg-blue-100 text-blue-700';
      case 'low':    return 'bg-green-100 text-green-700';
      default:       return 'bg-gray-100 text-gray-600';
    }
  }

  getApptStatusClass(status: string): string {
    switch (status?.toLowerCase()) {
      case 'completed':  return 'bg-green-100 text-green-700';
      case 'scheduled':  return 'bg-blue-100 text-blue-700';
      case 'confirmed':  return 'bg-cyan-100 text-cyan-700';
      case 'cancelled':  return 'bg-gray-200 text-gray-600';
      case 'no show':    return 'bg-red-100 text-red-700';
      default:           return 'bg-gray-100 text-gray-700';
    }
  }

  getBillStatusClass(status: string): string {
    switch (status) {
      case 'Paid':        return 'bg-green-100 text-green-700';
      case 'Pending':     return 'bg-orange-100 text-orange-700';
      case 'Partial':     return 'bg-blue-100 text-blue-700';
      case 'Cancelled':   return 'bg-gray-200 text-gray-600';
      case 'Draft':       return 'bg-purple-100 text-purple-700';
      case 'Written Off': return 'bg-red-100 text-red-700';
      default:            return 'bg-gray-100 text-gray-700';
    }
  }
}