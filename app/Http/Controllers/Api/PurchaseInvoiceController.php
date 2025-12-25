<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int)$request->per_page, 1), 50);

        return response()->json(
            PurchaseInvoice::latest()->paginate($perPage),
            200
        );
    }

    // POST /api/purchase-invoices
    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_number' => 'required|string|max:50|unique:purchase_invoices,invoice_number',
            'supplier_name'  => 'required|string|max:255',
            'items_count'    => 'required|integer|min:0',

            'subtotal'       => 'required|numeric|min:0',
            'tax'            => 'nullable|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',

            'payment_method' => 'required|in:cash,credit,bank_transfer,cheque,card',
            'status'         => 'required|in:pending,paid,cancelled',

            'notes'          => 'nullable|string',
        ]);

        $data['total'] =
            $data['subtotal']
            + ($data['tax'] ?? 0)
            - ($data['discount'] ?? 0);

        $invoice = PurchaseInvoice::create($data);

        return response()->json([
            'message' => 'Purchase invoice created successfully',
            'data' => $invoice
        ], 201);
    }

    // GET /api/purchase-invoices/{id}
    public function show($id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);

        return response()->json($invoice, 200);
    }

    // PUT /api/purchase-invoices/{id}
    public function update(Request $request, $id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);

        $data = $request->validate([
            'invoice_number' => 'sometimes|string|max:50|unique:purchase_invoices,invoice_number,' . $invoice->id,
            'supplier_name'  => 'sometimes|string|max:255',
            'items_count'    => 'sometimes|integer|min:0',

            'subtotal'       => 'sometimes|numeric|min:0',
            'tax'            => 'sometimes|numeric|min:0',
            'discount'       => 'sometimes|numeric|min:0',

            'payment_method' => 'sometimes|in:cash,credit,bank_transfer,cheque,card',
            'status'         => 'sometimes|in:pending,paid,cancelled',

            'notes'          => 'nullable|string',
        ]);

        $subtotal = $data['subtotal'] ?? $invoice->subtotal;
        $tax      = $data['tax'] ?? $invoice->tax;
        $discount = $data['discount'] ?? $invoice->discount;

        $data['total'] = $subtotal + $tax - $discount;

        $invoice->update($data);

        return response()->json([
            'message' => 'Purchase invoice updated successfully',
            'data' => $invoice
        ]);
    }

    // DELETE /api/purchase-invoices/{id}
    public function destroy($id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);
        $invoice->delete();

        return response()->json([
            'message' => 'Purchase invoice deleted successfully'
        ], 200);
    }
}
