<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\CourseCatalogue\DTOs\StoreNoticeData;
use Modules\CourseCatalogue\DTOs\UpdateNoticeData;
use Modules\CourseCatalogue\Models\Notice;

class NoticeController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

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

    public function store(StoreNoticeData $data): JsonResponse
    {
        $notice = Notice::create($data->toArray());

        $this->auditLogger->record('notice.create', 'notice', $notice->id, after: $notice->toArray());

        return response()->json(['data' => $notice], 201);
    }

    public function update(UpdateNoticeData $data, int $id): JsonResponse
    {
        $notice = Notice::findOrFail($id);

        $before = $notice->only(array_keys($data->toArray()));
        $notice->update($data->toArray());

        $this->auditLogger->record('notice.update', 'notice', $notice->id, before: $before, after: $data->toArray());

        return response()->json(['data' => $notice]);
    }

    public function destroy(int $id): JsonResponse
    {
        $notice = Notice::findOrFail($id);
        $notice->delete();

        $this->auditLogger->record('notice.delete', 'notice', $id);

        return response()->json(null, 204);
    }
}
