<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Services\VisitService;
use Exception;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class VisitController extends Controller
{
    use ApiResponse;

    public function __construct(private VisitService $visitService) {}

    // Get visits list with filters + stats
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['status', 'visit_date', 'patient_name']);

            $visits = $this->visitService->getFilteredVisits($filters);
            $stats  = $this->visitService->getVisitsStats();

            return $this->success($visits, 'Visits list fetched successfully.', 200, $stats);
        } catch (Exception $e) {
            return $this->error('Unable to fetch visits list.'. $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $visit = Visit::with([
                'appointment.patientCase.patient',
                'bill'
            ])->findOrFail($id);

            return $this->success($visit, 'Visit detail fetched successfully.');
        } catch (Exception $e) {
            return $this->error('Visit data not found.');
        }
    }
}