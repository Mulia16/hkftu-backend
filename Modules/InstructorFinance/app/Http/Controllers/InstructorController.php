<?php

namespace Modules\InstructorFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\InstructorProfile;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\InstructorFinance\Models\InstructorContract;

class InstructorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InstructorProfile::with('user')
            ->whereHas('user.roles', fn ($q) => $q->where('name', 'instructor'));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $instructors = $query->paginate($request->integer('per_page', 25));

        return response()->json($instructors);
    }

    public function show(int $id): JsonResponse
    {
        $instructor = InstructorProfile::with('user')
            ->whereHas('user.roles', fn ($q) => $q->where('name', 'instructor'))
            ->findOrFail($id);

        $contracts = InstructorContract::where('instructor_id', $instructor->user_id)
            ->with(['class.course.subject', 'class.centre'])
            ->get();

        return response()->json([
            'data' => $instructor,
            'contracts' => $contracts,
        ]);
    }

    public function teachingSummary(int $id, Request $request): JsonResponse
    {
        $instructor = InstructorProfile::findOrFail($id);

        $query = CourseClass::where('instructor_id', $instructor->user_id)
            ->with(['course.subject', 'course.season', 'centre', 'classroom']);

        if ($seasonId = $request->input('season_id')) {
            $query->whereHas('course', fn ($q) => $q->where('season_id', $seasonId));
        }

        $classes = $query->with(['course.subject', 'course.season', 'centre', 'classroom', 'sessions'])->orderByDesc('start_date')->get();

        $summary = [
            'total_classes' => $classes->count(),
            'total_sessions' => $classes->sum(fn ($c) => $c->sessions->count()),
            'classes' => $classes,
        ];

        return response()->json(['data' => $summary]);
    }

    public function generateContract(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer'],
            'instructor_id' => ['required', 'integer'],
        ]);

        $class = CourseClass::findOrFail($request->integer('class_id'));

        $contract = InstructorContract::updateOrCreate(
            [
                'class_id' => $class->id,
                'instructor_id' => $request->integer('instructor_id'),
            ],
            [
                'status' => 'draft',
            ]
        );

        return response()->json(['data' => $contract], 201);
    }

    public function signInSheet(int $id): JsonResponse
    {
        $instructor = InstructorProfile::findOrFail($id);

        $classes = CourseClass::where('instructor_id', $instructor->user_id)
            ->where('status', 'published')
            ->with(['course.subject', 'centre', 'classroom', 'sessions' => function ($q) {
                $q->orderBy('date')->orderBy('start_time');
            }])
            ->get();

        $sheets = $classes->map(function ($class) {
            return [
                'class_id' => $class->id,
                'class_code' => $class->class_code,
                'subject' => $class->course?->subject?->name,
                'centre' => $class->centre?->name,
                'classroom' => $class->classroom?->name,
                'sessions' => $class->sessions->map(function ($s) {
                    return [
                        'session_no' => $s->session_no,
                        'date' => $s->date,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                    ];
                }),
            ];
        });

        return response()->json(['data' => $sheets]);
    }
}
