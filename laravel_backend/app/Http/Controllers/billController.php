<?php

namespace App\Http\Controllers;

use App\Exports\BillsExport;
use Illuminate\Http\Request;
use App\Models\Bill;
use Exception;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\BillService;
use App\Services\DocumentService;
use App\Services\SettingService;

class billController extends Controller
{
    use ApiResponse;

    public function __construct(
        private BillService $billService,
        private SettingService $settingService,
        private DocumentService $documentService
    ) {}

    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'status',
                'start_date',
                'end_date',
                'min_amount',
                'max_amount',
                'patient_name',
                'limit'
            ]);

            $bills = $this->billService->getFilteredBills($filters);
            $stats = $this->billService->getBillStats($filters);

            return $this->success($bills, 'Bills retrieved successfully', 200, $stats);
        } catch (Exception $e) {
            return $this->error('Unable to fetch bills.');
        }
    }

    public function store(Request $request)
    {
        // Validation checks before opening transaction
        $exists = Bill::where('visit_id', $request->visit_id)
            ->whereNotIn('status', ['Draft'])
            ->exists();

        if ($exists) {
            return $this->error('A bill has already been generated for this visit.', 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $billAmount = $this->billService->calculateBillAmount(
                $data['charges'],
                $data['insurance_coverage'],
                $data['discount_amount'],
                $data['tax_amount']
            );

            // Delete any existing draft for this visit
            Bill::where('visit_id', $request->visit_id)
                ->where('status', 'Draft')
                ->delete();

            $bill = Bill::create([
                'visit_id'          => $data['visit_id'],
                'bill_date'         => now(),
                'insurance_firm_id' => $data['insurance_firm_id'],
                'created_by'        => $data['created_by'],
                'procedure_codes'   => $data['procedure_codes'],
                'charges'           => $data['charges'],
                'insurance_coverage' => $data['insurance_coverage'],
                'discount_amount'   => $data['discount_amount'],
                'tax_amount'        => $data['tax_amount'],
                'bill_amount'       => $billAmount,
                'outstanding_amount' => $billAmount,
                'paid_amount'       => $data['paid_amount'],
                'status'            => $data['status'],
                'due_date'          => $data['due_date'],
                'notes'             => $data['notes'],
            ]);

            // Draft — no PDF generation needed
            if ($data['status'] === 'Draft') {
                DB::commit();
                return $this->success($bill, 'Bill saved as draft successfully.');
            }

            // Load relationships needed for PDF generation
            $bill->load(
                'visit.appointment.patientCase.patient',
                'visit.appointment.patientCase.nf2Detail',
                'insurance_firm',
                'payments'
            );

            $settings = $this->settingService->getSettings();

            $this->documentService->generateBillDocuments($bill, $settings);
            DB::commit();

            return $this->success($bill, "Bill created successfully");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to generate bill: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $bill = Bill::with([
                'visit.appointment.patientCase.patient',
                'insurance_firm',
                'payments'
            ])->findOrFail($id);
            return $this->success($bill, 'Bill detail fetched successfully.');
        } catch (Exception $e) {
            return $this->error('Bill data not found.');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $bill = Bill::findOrFail($id);

            if ($bill->paid_amount > 0 && $bill->status !== 'Draft') {
                DB::rollBack();
                return $this->error('Cannot edit a bill that has payments posted against it.', 403);
            }

            $bill = $this->billService->updateBill($bill, $request->all());

            $settings = $this->settingService->getSettings();
            $this->documentService->generateInvoice($bill, $settings);
            DB::commit();

            return $this->success($bill, 'Bill updated and recalculated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update bill.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bill = Bill::findOrFail($id);

            if (in_array($bill->status, ['Partial', 'Paid', 'Written Off'])) {
                DB::rollBack();
                return $this->error('Cannot delete a bill with payments posted against it.', 422);
            }

            $bill->delete();

            DB::commit();
            return $this->success(null, 'Bill deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to delete the bill.');
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(
                new BillsExport($request->all()),
                'bills.xlsx'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'status' => 'required|in:Cancelled,Written Off'
            ]);

            $bill = $this->billService->updateBillStatus($id, $request->status);

            DB::commit();
            return $this->success($bill, 'Bill status updated successfully.');
        }  catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 422);
        }
    }
}
