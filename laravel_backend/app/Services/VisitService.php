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
       $searchTerm = isset($filters['patient_name'])
       ? '%' . str_replace(' ', '%', $filters['patient_name']) . '%'
       : null;
 
       $query->when($filters['patient_name'] ?? null, function ($q) use ($filters, $searchTerm) {
        $q->whereHas('appointment.patientCase.patient', function ($sub) use ($filters, $searchTerm) {
        $sub->where('first_name', 'like', '%' . $filters['patient_name'] . '%')
            ->orWhere('middle_name', 'like', '%' . $filters['patient_name'] . '%')
            ->orWhere('last_name', 'like', '%' . $filters['patient_name'] . '%')
            ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", [$searchTerm]);
    });
});
 

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