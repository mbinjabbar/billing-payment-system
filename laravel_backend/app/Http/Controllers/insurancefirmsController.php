<?php

namespace App\Http\Controllers;

use App\Models\InsuranceFirm;
use Illuminate\Http\Request;
use Exception;
use App\Traits\ApiResponse;

class insurancefirmsController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = InsuranceFirm::query();

            if ($request->boolean('active_only')) {
                $activeInsuranceFirms = $query->where('is_active', true)->get();
                return $this->success($activeInsuranceFirms, 'Insurance firms retrieved successfully');
            }

            $insuranceFirms = $query->latest()->paginate(10);

            return $this->success($insuranceFirms, 'Insurance firms retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Unable to fetch insurance firms.');
        }
    }


    public function store(Request $request)
    {

        try {
            $data = $request->validate([
                'name' => 'required|unique:insurance_firms,name',
                'firm_type' => 'required|in:Auto,Health',
                'contact_person' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'carrier_code' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $insuranceFirm = InsuranceFirm::create($data);
            return $this->success($insuranceFirm, 'Insurance firm created successfully', 201);
        } catch (Exception $e) {
            return $this->error('Unable to create insurance firm.');
        }
    }


    public function show($id)
    {
        try {
            $insuranceFirm = InsuranceFirm::findOrFail($id);
            return $this->success($insuranceFirm, 'Insurance firm retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Unable to fetch insurance firm.');
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required|unique:insurance_firms,name,' . $id,
                'firm_type' => 'required|in:Auto,Health',
                'contact_person' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'carrier_code' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $insuranceFirm = InsuranceFirm::findOrFail($id);
            $insuranceFirm->update($data);
            return $this->success($insuranceFirm, 'Insurance firm updated successfully');
        } catch (Exception $e) {
            return $this->error('Unable to update insurance firm.');
        }
    }


    public function destroy($id)
    {
        try {
            $insuranceFirm = InsuranceFirm::findOrFail($id);
            $insuranceFirm->delete();
            return $this->success(null, 'Insurance firm deleted successfully');
        } catch (Exception $e) {
            return $this->error('Unable to delete insurance firm.');
        }
    }
}
