<?php

namespace App\Http\Controllers;
use App\Models\InsuranceFirm;
use Illuminate\Http\Request;
use Exception;
use App\Traits\ApiResponse;

class insurancefirmsController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $insuranceFirms = InsuranceFirm::all();
            return $this->success($insuranceFirms, 'Insurance firms retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Unable to fetch insurance firms.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
