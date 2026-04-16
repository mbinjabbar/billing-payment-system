<?php

namespace App\Services;

use App\Models\Visit;

class VisitService
{
    public function getFilteredVisits(array $filters)
    {
        $query = Visit::with(['appointment.patientCase.patient', 'bill']);

        $query->when(
            $filters['status'] ?? null,
            fn($q) => $q->where('status', $filters['status'])
        );

        $query->when(
            $filters['visit_date'] ?? null,
            fn($q) => $q->whereDate('visit_date', $filters['visit_date'])
        );

        $query->when(
            $filters['patient_name'] ?? null,
            fn($q) =>
            $q->whereHas(
                'appointment.patientCase.patient',
                fn($sub) =>
                $sub->where('first_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhere('middle_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhere('last_name', 'like', '%' . $filters['patient_name'] . '%')
            )
        );

        return $query->latest('visit_date')->paginate(10);
    }

    public function getVisitsStats()
    {
        return [
            'total_visits' => Visit::count(),
            'billed' => Visit::whereHas('bill', fn($q) => $q->whereNotIn('status', ['Draft']))->count(),
            'unbilled'       => Visit::where('status', 'Completed')->doesntHave('bill')->count()
        ];
    }
}
