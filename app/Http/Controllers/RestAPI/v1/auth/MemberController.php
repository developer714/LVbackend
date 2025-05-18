<?php

namespace App\Http\Controllers\RestAPI\v1\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Utils\Helpers;
use Illuminate\Support\Str;
use App\Models\User;

class MemberController extends Controller {
    /**
    * Display a listing of the resource.
    */

    public function index() {
        $users = User::all();
        // Using User model
        return response()->json( $users );
    }

    /**
    * Store a newly created resource in storage.
    */

    public function store( Request $request ) {
        try {
            // Check if email exists
            $existingUser = User::where( 'email', $request->email )->first();
            if ( $existingUser ) {
                return response()->json( [
                    'status' => false,
                    'message' => 'Email already exists',
                    'errors' => [ 'email' => [ 'This email is already registered' ] ]
                ], 422 );
            }

            $validatedData = $request->validate( [
                'f_name' => 'required',
                'l_name' => 'required',
                'email' => 'required|unique:users',
                'phone' => 'required|unique:users',
                'password' => 'required|min:8',
                'branch_id' => 'required',
                'rank' => 'required',
            ] );

            if ( $request->referral_code ) {
                $refer_user = User::where( [ 'referral_code' => $request->referral_code ] )->first();
            }
            // Hash the password before saving
            $validatedData[ 'password' ] = bcrypt( $validatedData[ 'password' ] );

            $temporary_token = Str::random( 40 );
            $user = User::create( [
                'f_name' => $request->f_name,
                'l_name' => $request->l_name, 
                'image' => $request->image,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => 1,
                'password' => bcrypt( $request->password ?? "12345678" ),
                'recommender_name' => $request->recommender_name,
                'rank' => $request->rank,
                'concierge' => $request->concierge,
                'branch_id' => $request->branch_id,
                'bank' => $request->bank,
                'suggestion' => $request->suggestion,
                'mountains_and_rivers' => $request->mountains_and_rivers,
                'community_pv' => $request->community_pv,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
                'circulation_rate' => $request->circulation_rate,
                'suspension_of_benefits' => $request->suspension_of_benefits,
                'zip' => $request->zip,
                'address' => $request->address,
                'status' => $request->status,
                'payment_and_amount' => $request->payment_and_amount,
                'verification_code' => $request->verification_code,
                'temporary_token' => $temporary_token,
                'referral_code' => Helpers::generate_referer_code(),
                'referred_by' => ( isset( $refer_user ) && $refer_user ) ? $refer_user->id : null,
            ] );

            return response()->json( [
                'status' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201 );

        } catch ( \Exception $e ) {
            return response()->json( [
                'status' => false,
                'message' => 'Error creating user',
                'errors' => $e->getMessage()
            ], 500 );
        }
    }

    /**
    * Display the specified resource.
    */

    public function show( string $id ) {
        $user = User::findOrFail( $id );
        // Using User model
        return response()->json( $user );
    }

    /**
    * Show the form for editing the specified resource.
    */

    public function edit( string $id ) {
        $user = User::findOrFail( $id );
        // Using User model
        // Return user data for editing
        return response()->json( $user );
    }
    /**
    * Update the specified resource in storage.
    */

    public function update(Request $request, string $id) {
        try {
            $user = User::findOrFail($id);
            
            // Define fillable fields
            $fillableFields = [
                'f_name', 'l_name', 'email', 'phone', 'image', 'recommender_name',
                'rank', 'concierge', 'branch_id', 'bank', 'suggestion',
                'mountains_and_rivers', 'community_pv', 'account_number',
                'account_holder', 'circulation_rate', 'suspension_of_benefits',
                'zip', 'address', 'status', 'payment_and_amount', 'verification_code'
            ];

            // Build update data dynamically
            $updateData = collect($fillableFields)
                ->mapWithKeys(function ($field) use ($request, $user) {
                    return [$field => $request->get($field, $user->$field)];
                })
                ->toArray();

            // Handle password separately
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            // Update user using instance method
            $user->update($updateData);

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating user',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
    * Remove the specified resource from storage.
    */

    public function destroy( string $id ) {
        $user = User::findOrFail( $id );
        // Using User model
        $user->delete();
        return response()->json( null, 204 );
        // No content to signify successful deletion
    }
}
