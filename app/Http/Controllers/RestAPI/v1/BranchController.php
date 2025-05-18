<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Branch;

class BranchController extends Controller {
    /**
    * Display a listing of the resource.
    */

    public function index() {
        $branches = Branch::all();

        return response()->json([
            'status' => 'success',
            'data' => $branches
        ], 200);
    }

    /**
    * Store a newly created resource in storage.
    */

    public function store( Request $request ) {
        $validator = Validator::make($request->all(), rules: [ 
            'branch_name' => 'required|string',
            'number_of_members' => 'nullable|integer',
            'branch_manager' => 'nullable|string',
            'cell_phone' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'fax_number' => 'nullable|string',
            'account_number' => 'nullable|string',
            'deposit_withdrawal_history' => 'nullable|string',
            'affiliated_members' => 'nullable|string',
            'stop_allowance' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $branch = Branch::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $branch
        ], 201);
    }

    /**
    * Display the specified resource.
    */

    public function show( string $id ) {
        $branch = Branch::find($id);
        
        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Branch not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $branch
        ], 200);
    }

    /**
    * Show the form for editing the specified resource.
    */

    public function edit( string $id ) {
        $branch = Branch::findOrFail( $id );
        // Using Branch model
        // Return branch data for editing
        return response()->json( $branch );
    }

    /**
    * Update the specified resource in storage.
    */

    public function update(Request $request, string $id) {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Branch not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [ 
            'branch_name' => 'string',
            'number_of_members' => 'nullable|integer',
            'branch_manager' => 'nullable|string',
            'cell_phone' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'fax_number' => 'nullable|string',
            'account_number' => 'nullable|string',
            'deposit_withdrawal_history' => 'nullable|string',
            'affiliated_members' => 'nullable|string',
            'stop_allowance' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $branch->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $branch
        ], 200);
    }

    /**
    * Remove the specified resource from storage.
    */

    public function destroy( string $id ) {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Branch not found'
            ], 404);
        }

        $branch->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Branch deleted successfully'
        ], 200);
    }
}
