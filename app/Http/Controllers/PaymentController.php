<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with('user')->get();
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'courses' => 'required|array',
            'courses.*.course_name' => 'required|string|max:255',
            'courses.*.amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,failed,refunded'
        ]);

        $payment = Payment::create($validated);
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return response()->json($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'courses' => 'sometimes|required|array',
            'courses.*.course_name' => 'required_with:courses|string|max:255',
            'courses.*.amount' => 'required_with:courses|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:pending,completed,failed,refunded'
        ]);

        $payment->update($validated);
        return response()->json($payment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(null, 204);
    }
}
