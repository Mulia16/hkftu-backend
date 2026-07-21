<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notification\Models\SupportTicket;
use Modules\Notification\Services\NotificationService;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationService $service): JsonResponse
    {
        $logs = $service->getLogs(
            $request->input('channel'),
            $request->input('status'),
        )->paginate($request->input('per_page', 25));

        return response()->json($logs);
    }

    public function send(Request $request, NotificationService $service): JsonResponse
    {
        $request->validate([
            'channel' => ['required', 'string', 'in:email,sms,push'],
            'recipient' => ['required', 'string'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        $log = $service->send(
            $request->input('channel'),
            $request->input('recipient'),
            $request->input('subject'),
            $request->input('body'),
        );

        return response()->json(['data' => $log], 201);
    }

    public function storeTicket(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
        ]);

        return response()->json(['data' => $ticket], 201);
    }

    public function tickets(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['user', 'responder']);

        if (! $request->user()->hasAnyRole(['system_admin', 'centre_manager', 'counter_staff'])) {
            $query->where('user_id', $request->user()->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 25));
        return response()->json($tickets);
    }

    public function respondTicket(Request $request, int $id): JsonResponse
    {
        $request->validate(['response' => ['required', 'string']]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'response' => $request->input('response'),
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
            'status' => 'resolved',
        ]);

        return response()->json(['data' => $ticket->load(['user', 'responder'])]);
    }
}
