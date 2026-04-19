<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Patient::query();

            $query->when(
                $request->search ?? null,
                fn($q) => $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('middle_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name',   'like', '%' . $request->search . '%')
                    ->orWhere('phone',       'like', '%' . $request->search . '%')
                    ->orWhere('email',       'like', '%' . $request->search . '%')
            );

            $patients = $query->latest()->paginate(10);
            return $this->success($patients, 'Patients retrieved successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to fetch patients.');
        }
    }

    public function show($id)
    {
        try {
            $patient = Patient::with([
                'cases.nf2Detail',
                'cases.appointments.visit.bill',
            ])->findOrFail($id);

            return $this->success($patient, 'Patient details retrieved successfully.');
        } catch (Exception $e) {
            return $this->error('Patient not found.');
        }
    }
}