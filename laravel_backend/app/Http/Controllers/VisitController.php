<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use log;
use Exception;


class VisitController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);
        try {
            $visit = Visit::where('appointment_id', $validated['appointment_id'])->with(['appointment.case.patient', 'bill'])->firstOrFail();
            return response()->json([
                'success' => true,
                'data' => $visit
            ], 200);
        } catch (Exception $e) {
            Log::error('error fetching visits list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'error fetching visits list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getVisitDetails(Request $request, $visitId)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
        ]);
        try {
            $visit = Visit::with(['appointment.case.patient', 'bill'])->findOrFail($visitId);
            return response()->json([
                'success' => true,
                'data' => $visit
            ], 200);
        } catch (Exception $e) {
            Log::error('error fetching a single visit detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'error fetching a single visit detail.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
