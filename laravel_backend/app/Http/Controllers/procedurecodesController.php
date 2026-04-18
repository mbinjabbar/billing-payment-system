<?php

namespace App\Http\Controllers;
use App\Models\ProcedureMaster;
use Illuminate\Http\Request;
use Exception;
use App\Traits\ApiResponse;

class procedurecodesController extends Controller
{
    use ApiResponse;
  
    public function index(Request $request)
    {
        try {
        $query = ProcedureMaster::query();

        if($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        
        $procedureCodes = $query->get();
        return $this->success($procedureCodes, 'Procedure codes retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Unable to fetch procedure codes.');
        }
        
    }

   
    public function store(Request $request)
    {
            try {
               
                $data= $request->validate([
                    'code' => 'required|unique:procedure_masters,code',
                    'name' => 'required',
                    'standard_charge' => 'required|numeric|decimal:2|between:0,99999999.99',
                    'is_active' => 'boolean'
                ]);


                $procedureCode = ProcedureMaster::create($data);
                return $this->success($procedureCode, 'Procedure code created successfully', 201);
            } catch (Exception $e) {
                return $this->error('Failed to create procedure code.');
            }
    }

    
    public function show($id)
    {
        try {
            $procedureCode = ProcedureMaster::findOrFail($id);
            return $this->success($procedureCode, 'Procedure code retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Unable to fetch procedure code.');
        }
    }

    
    public function update(Request $request,$id)
    {
        try {
            $data = $request->validate([
                'code' => 'unique:procedure_masters,code,' . $id,
                'standard_charge' => 'numeric|decimal:2|between:0,99999999.99',
                'is_active' => 'boolean'
            ]);

            $procedureCode = ProcedureMaster::findOrFail($id);
            $procedureCode->update($data);
            return $this->success($procedureCode, 'Procedure code updated successfully');
        } catch (Exception $e) {
            return $this->error('Failed to update procedure code.');
        }
    }

  
    public function destroy($id)
    {
        try {
            $procedureCode = ProcedureMaster::findOrFail($id);
            $procedureCode->delete();
            return $this->success(null, 'Procedure code deleted successfully');
        } catch (Exception $e) {
            return $this->error('Unable to delete procedure code.');
        }
    }
}
