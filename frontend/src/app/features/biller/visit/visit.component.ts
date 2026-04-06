import { Component } from '@angular/core';

@Component({
  selector: 'app-visit',
  templateUrl: './visit.component.html',
})
export class VisitComponent {
  totalPending = 12840;

  visits = [
    {
      initials: 'SM',
      patientName: 'Sarah Mitchell',
      id: '#PX-9920',
      date: 'Oct 24, 2023',
      time: '09:15 AM',
      service: 'Comprehensive Physical Exam',
      icd: 'ICD-10: Z00.00',
    },
    {
      initials: 'JW',
      patientName: 'James Wilson',
      id: '#PX-8142',
      date: 'Oct 24, 2023',
      time: '10:30 AM',
      service: 'Diagnostic Ultrasound: Abdominal',
      icd: 'ICD-10: R10.9',
    },
    {
      initials: 'EK',
      patientName: 'Elena Kovach',
      id: '#PX-4421',
      date: 'Oct 23, 2023',
      time: '02:45 PM',
      service: 'Cardiac Follow-up Consultation',
      icd: 'ICD-10: I10',
    },
    {
      initials: 'MT',
      patientName: 'Marcus Thorne',
      id: '#PX-2209',
      date: 'Oct 23, 2023',
      time: '11:15 AM',
      service: 'Blood Lab Panel - Standard',
      icd: 'ICD-10: E78.5',
    },
  ];
}