<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(): JsonResponse
    {
        $notices = Notice::where('is_active', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        return response()->json(['data' => $notices]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $notices = Notice::orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['data' => $notices]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $notice = Notice::create($data);

        return response()->json(['data' => $notice], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $notice = Notice::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $notice->update($data);

        return response()->json(['data' => $notice]);
    }

    public function destroy(int $id): JsonResponse
    {
        Notice::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
