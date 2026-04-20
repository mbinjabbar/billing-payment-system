<?php

namespace App\Services;

use App\Models\Visit;

class VisitService
{
    // ── Get visits with filters + pagination
    public function getFilteredVisits(array $filters)
    {
        $query = Visit::with(['appointment.patientCase.patient', 'bill']);

        // filter by visit status
        $query->when(
            $filters['status'] ?? null,
            fn($q) => $q->where('status', $filters['status'])
        );

        // filter by specific visit date
        $query->when(
            $filters['visit_date'] ?? null,
            fn($q) => $q->whereDate('visit_date', $filters['visit_date'])
        );

        // search by patient name
        $query->when(
            $filters['patient_name'] ?? null,
            fn($q) =>
            $q->whereHas(
                'appointment.patientCase.patient',
                fn($sub) =>
                $sub->where('first_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhere('middle_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhere('last_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $filters['patient_name'] . '%'])
            )
        );

        return $query->latest('visit_date')->paginate(10);
    }

    // ── Get visit statistics for dashboard
    public function getVisitsStats()
    {
        return [
            'total_visits' => Visit::count(),

            // visits that already have a bill (excluding drafts)
            'billed' => Visit::whereHas(
                'bill',
                fn($q) => $q->whereNotIn('status', ['Draft'])
            )->count(),

            // completed visits without any bill yet
            'unbilled' => Visit::where('status', 'Completed')
                ->doesntHave('bill')
                ->count()
        ];
    }
}