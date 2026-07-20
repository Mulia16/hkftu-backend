<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CourseCatalogue\DTOs\StoreCategoryData;
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

    public function store(StoreCategoryData $data): JsonResponse
    {
        $validated = $data->toArray();

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(preg_replace('/[^a-z0-9]+/', '_', strtolower($validated['name_en'])));
        }

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Category::where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') + 1;
        }

        $category = Category::create($validated);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:course_catalogue.categories,id',
            'name_en' => 'sometimes|string|max:255',
            'name_zh' => 'sometimes|string|max:255',
        ]);

        if (isset($validated['name_en']) && !isset($validated['code'])) {
            $validated['code'] = strtoupper(preg_replace('/[^a-z0-9]+/', '_', strtolower($validated['name_en'])));
        }

        $category->update($validated);

        return response()->json(['data' => $category]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['data' => ['message' => 'Category deleted.']]);
    }
}
