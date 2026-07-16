<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\LearnerProfile;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Models\Receipt;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalLearners = LearnerProfile::count();
        $activeClasses = CourseClass::where('status', 'published')->count();
        $totalEnrolments = Enrolment::where('status', 'confirmed')->count();
        $totalRevenue = Receipt::where('status', 'issued')->sum('amount');
        $pendingPayments = PaymentIntent::where('status', 'pending')->count();
        $fullClasses = CourseClass::where('status', 'published')
            ->whereRaw('(SELECT COUNT(*) FROM enrolment.enrolments WHERE class_id = class_scheduling.classes.id AND status = \'confirmed\') >= capacity')
            ->count();
        $dangerClasses = CourseClass::where('status', 'published')
            ->whereRaw('(SELECT COUNT(*) FROM enrolment.enrolments WHERE class_id = class_scheduling.classes.id AND status = \'confirmed\') < min_students')
            ->count();
        $recentEnrolments = Enrolment::with(['learner', 'courseClass.course.subject'])
            ->where('status', 'confirmed')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'total_learners' => $totalLearners,
                'active_classes' => $activeClasses,
                'total_enrolments' => $totalEnrolments,
                'total_revenue' => $totalRevenue,
                'pending_payments' => $pendingPayments,
                'full_classes' => $fullClasses,
                'danger_classes' => $dangerClasses,
                'recent_enrolments' => $recentEnrolments,
            ],
        ]);
    }
}
