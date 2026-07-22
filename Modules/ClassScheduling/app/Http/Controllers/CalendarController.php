<?php

namespace Modules\ClassScheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Ownership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\Enrolment\Models\Enrolment;

class CalendarController extends Controller
{
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ClassSession::query()
            ->whereHas('courseClass')
            ->with(['courseClass.course.subject', 'courseClass.centre']);

        if ($from = $request->input('from')) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('date', '<=', $to);
        }

        if (Ownership::isStaff($user)) {
            if ($centreId = $request->input('centre_id')) {
                $query->whereHas('courseClass', fn ($q) => $q->where('centre_id', $centreId));
            }
        } elseif ($user->hasRole('instructor')) {
            $query->whereHas('courseClass', fn ($q) => $q->where('instructor_id', $user->id));
        } else {
            $classIds = Enrolment::where('learner_id', $user->learnerProfile?->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->pluck('class_id');

            $query->whereIn('class_id', $classIds);
        }

        $events = $query->orderBy('date')->orderBy('start_time')->get()->map(fn (ClassSession $session) => [
            'id' => $session->id,
            'date' => $session->date?->toDateString(),
            'start_time' => substr((string) $session->start_time, 0, 5),
            'end_time' => substr((string) $session->end_time, 0, 5),
            'status' => $session->status,
            'class_code' => $session->courseClass?->class_code,
            'title' => $session->courseClass?->course?->subject?->name ?? $session->courseClass?->class_code,
            'centre' => $session->courseClass?->centre?->name,
        ]);

        return response()->json(['data' => $events]);
    }
}
