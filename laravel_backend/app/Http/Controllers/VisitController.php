<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Exception;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class VisitController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Visit::with(['appointment.patientCase.patient', 'bill']);

            $query->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            });

            $query->when($request->visit_date, function ($q) use ($request) {
                return $q->whereDate('visit_date', $request->visit_date);
            });

            $query->when($request->patient_name, function ($q) use ($request) {
                return $q->whereHas('appointment.patientCase.patient', function ($sub) use ($request) {
                    $sub->where('first_name', 'like', '%' . $request->patient_name . '%')
                        ->orWhere('middle_name', 'like', '%' . $request->patient_name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
                });
            });

            $stats = [
                'total_visits' => (clone $query)->count(),
                'billed' => (clone $query)->whereHas('bill')->count(),
                'unbilled'       => (clone $query)->where('status', 'Completed')->doesntHave('bill')->count()
            ];

            $visits = $query->latest('visit_date')->paginate(10);

            return $this->success($visits, 'Visits list fetched successfully.', 200, $stats);
        } catch (Exception $e) {
            return $this->error('Unable to fetch visits list.');
        }
    }

    public function show($id)
    {
        try {
            $visit = Visit::with(['appointment.patientCase.patient', 'bill'])->findOrFail($id);
            return $this->success($visit, 'Visit detail fetched successfully.');
        } catch (Exception $e) {
            return $this->error('Visit data not found.');
        }
    }
}
