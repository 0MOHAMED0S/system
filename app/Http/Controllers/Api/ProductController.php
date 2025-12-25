<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class ProductController extends Controller
{
    const MAX_QUANTITY = 1000000;
    const MAX_PRICE = 1000000;

    // GET /api/products
    public function index(Request $request)
    {
        try {
            $request->validate([
                'per_page' => 'sometimes|integer|min:1|max:100'
            ]);

            $perPage = $request->get('per_page', 10);

            $products = Product::with('category')
                ->latest()
                ->paginate($perPage);

            return response()->json($products, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid query parameters',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    // POST /api/products
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id'  => 'required|exists:categories,id',
                'name'         => 'required|string|max:255|unique:products,name',
                'cost_price'   => 'required|numeric|min:0|max:' . self::MAX_PRICE,
                'sale_price'   => 'required|numeric|min:0|max:' . self::MAX_PRICE . '|gte:cost_price',
                'quantity'     => 'required|integer|min:0|max:' . self::MAX_QUANTITY,
                'min_quantity' => 'required|integer|min:0|max:' . self::MAX_QUANTITY,
            ]);

            $product = Product::create($data);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create product'
            ], 500);
        }
    }

    // GET /api/products/{id}
    public function show($id)
    {
        try {
            $product = Product::with('category')->findOrFail($id);

            return response()->json($product, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    // PUT /api/products/{id}
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $data = $request->validate([
                'category_id'  => 'sometimes|exists:categories,id',
                'name'         => 'sometimes|string|max:255|unique:products,name,' . $product->id,
                'cost_price'   => 'sometimes|numeric|min:0|max:' . self::MAX_PRICE,
                'sale_price'   => 'sometimes|numeric|min:0|max:' . self::MAX_PRICE,
                'quantity'     => 'sometimes|integer|min:0|max:' . self::MAX_QUANTITY,
                'min_quantity' => 'sometimes|integer|min:0|max:' . self::MAX_QUANTITY,
            ]);

            // لو السعرين موجودين
            if (
                isset($data['cost_price'], $data['sale_price']) &&
                $data['sale_price'] < $data['cost_price']
            ) {
                return response()->json([
                    'message' => 'Sale price must be greater than or equal to cost price'
                ], 422);
            }

            $product->update($data);

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update product'
            ], 500);
        }
    }

    // DELETE /api/products/{id}
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product'
            ], 500);
        }
    }
}
