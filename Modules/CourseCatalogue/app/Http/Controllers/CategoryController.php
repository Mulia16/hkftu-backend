<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CourseCatalogue\Models\Category;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('children');

        return response()->json(['data' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:course_catalogue.categories,id',
            'code' => 'required|string|max:50|unique:course_catalogue.categories,code',
            'name_en' => 'required|string|max:255',
            'name_zh' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create($validated);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:course_catalogue.categories,id',
            'code' => 'sometimes|string|max:50|unique:course_catalogue.categories,code,' . $category->id,
            'name_en' => 'sometimes|string|max:255',
            'name_zh' => 'sometimes|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update($validated);

        return response()->json(['data' => $category]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['data' => ['message' => 'Category deleted.']]);
    }
}
