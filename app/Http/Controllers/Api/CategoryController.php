<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CategoryController extends Controller
{
    // GET /api/categories
public function index(Request $request)
{
    try {
        $request->validate([
    'per_page' => 'sometimes|integer|min:1|max:100'
]);

        $perPage = $request->get('per_page', 10);

        $perPage = min(max((int)$perPage, 1), 100);

        $categories = Category::latest()->paginate($perPage);

        return response()->json($categories, 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // POST /api/categories
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'      => 'required|string|max:255|unique:categories,name',
                'resource'  => 'nullable|string',
                'is_active' => 'required|boolean',
            ]);

            $category = Category::create($data);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/categories/{id}
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);

            return response()->json($category, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
    }

    // PUT /api/categories/{id}
    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $data = $request->validate([
                'name'      => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
                'resource'  => 'sometimes|nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            $category->update($data);

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
