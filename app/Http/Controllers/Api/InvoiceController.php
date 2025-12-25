<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int)$request->per_page, 1), 50);

        return response()->json(
            Invoice::latest()->paginate($perPage),
            200
        );
    }

    // POST /api/invoices
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'  => 'nullable|string|max:255',
            'total_amount'   => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,vodafone_cash,bank_transfer',
        ]);

        $invoice = Invoice::create($data);

        return response()->json([
            'message' => 'Invoice created successfully',
            'data'    => $invoice
        ], 201);
    }

    // GET /api/invoices/{id}
    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);

        return response()->json($invoice, 200);
    }

    // PUT /api/invoices/{id}
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $data = $request->validate([
            'customer_name'  => 'nullable|string|max:255',
            'total_amount'   => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|in:cash,vodafone_cash,bank_transfer',
        ]);

        $invoice->update($data);

        return response()->json([
            'message' => 'Invoice updated successfully',
            'data'    => $invoice
        ], 200);
    }

    // DELETE /api/invoices/{id}
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ], 200);
    }
}
