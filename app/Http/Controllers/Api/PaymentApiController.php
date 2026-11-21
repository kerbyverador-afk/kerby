<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentApiController extends Controller
{
    /**
     * Display a listing of the resource (READ ALL).
     * GET /api/payments
     */
    public function index()
    {
        $payments = Payment::with('user')->get();

        return response()->json([
            'status' => true,
            'message' => 'Payments retrieved successfully.',
            'data' => $payments,
        ], 200);
    }

    /**
     * Store a newly created resource in storage (CREATE).
     * POST /api/payments
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer|unique:payments,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'month_year' => 'nullable|string',
            'payment_date' => 'nullable|string',
            'status' => 'required|in:Paid,Unpaid,Due',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Kunin ang petsa alinman sa month_year o payment_date ang ipinasa sa Postman
        $dateValue = $request->month_year ?? $request->payment_date ?? now()->format('F Y');

        $dataToCreate = [
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'month_year' => $dateValue, // Itinatago ito sa tamang column sa database
            'status' => $request->status,
            'notes' => $request->notes ?? null,
        ];

        // Kung nagpasa ka ng custom ID mula sa Postman
        if ($request->filled('id')) {
            $dataToCreate['id'] = $request->id;
        }

        $payment = Payment::create($dataToCreate);

        return response()->json([
            'status' => true,
            'message' => 'Payment record created successfully.',
            'data' => $payment,
        ], 201);
    }

    /**
     * Display the specified resource (READ ONE).
     * GET /api/payments/{id}
     */
    public function show($id)
    {
        $payment = Payment::with('user')->find($id);

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment record not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment record retrieved successfully.',
            'data' => $payment,
        ], 200);
    }

    /**
     * Update the specified resource in storage (UPDATE).
     * PUT/PATCH /api/payments/{id}
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment record not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|exists:users,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'month_year' => 'nullable|string',
            'payment_date' => 'nullable|string',
            'status' => 'sometimes|required|in:Paid,Unpaid,Due',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dateValue = $request->month_year ?? $request->payment_date ?? $payment->month_year;

        $payment->update([
            'amount' => $request->amount ?? $payment->amount,
            'month_year' => $dateValue,
            'status' => $request->status ?? $payment->status,
            'notes' => $request->notes ?? $payment->notes,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment record updated successfully.',
            'data' => $payment,
        ], 200);
    }

    /**
     * Remove the specified resource from storage (DELETE).
     * DELETE /api/payments/{id}
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment record not found.',
            ], 404);
        }

        $payment->delete();

        return response()->json([
            'status' => true,
            'message' => 'Payment record deleted successfully.',
        ], 200);
    }
}
