<?php

namespace App\Http\Controllers;
use App\Models\ProcedureMaster;
use Illuminate\Http\Request;
use Exception;
use App\Traits\ApiResponse;

class procedurecodesController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
        $procedureCodes = ProcedureMaster::all();
        return $this->success($procedureCodes, 'Procedure codes retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Unable to fetch procedure codes.');
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
