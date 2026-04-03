<?php

namespace App\Http\Controllers;
use App\Models\Visit;
use Exception;
use App\Traits\ApiResponse;

class VisitController extends Controller
{
    use ApiResponse;
    public function index()
    {
        try {
            $visits = Visit::with(['appointment.patientCase.patient', 'bill'])->get();
            return $this->success(["visits" => $visits], 'Visits list fetched successfully.');
        } catch (Exception $e) {
            return $this->error('Unable to fetch visits list.'); 
        }
    }

    public function show($id)
    {
        try {
            $visit = Visit::with(['appointment.patientCase.patient', 'bill'])->findOrFail($id);
            return $this->success(["visit" => $visit], 'Visit detail fetched successfully.');
        } catch (Exception $e) {
            return $this->error('visit data not found.');
            
        }
    }
}
