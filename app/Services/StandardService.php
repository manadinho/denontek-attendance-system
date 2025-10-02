<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\Standard;
use App\Models\Student;
use Carbon\Carbon;

class StandardService
{
    public function getStandardTodayAttendance($schoolId, $standardId=null)
    {
        $settings = SchoolSetting::where('school_id', $schoolId)->first();
        if (!$settings) {
            info('No school settings found for school ID: '.$schoolId);
            return [];
        }

        $buffer = $settings->buffer_minutes ?? 0;

        // Build today's windows (no explicit TZ logic here)
        $today = Carbon::today(); // uses app timezone automatically
        $checkinStart   = Carbon::parse($today->toDateString().' '.$settings->checkin_start)->subMinutes($buffer);
        $checkinEnd     = Carbon::parse($today->toDateString().' '.$settings->checkin_end)->addMinutes($buffer);
        $checkoutStart  = Carbon::parse($today->toDateString().' '.$settings->checkout_start)->subMinutes($buffer);
        $checkoutEnd    = Carbon::parse($today->toDateString().' '.$settings->checkout_end)->addMinutes($buffer);

        // Students in this standard
        $studentsQuery = Student::where('school_id', $schoolId);

        if($standardId) {
            $studentsQuery->where('standard_id', $standardId);
        }

        $students = $studentsQuery->orderBy('created_at')->get();

        if ($students->isEmpty()) {
            return [];
        }

        $studentIds = $students->pluck('id');

        $attendances = Attendance::query()
            ->whereIn('student_id', $studentIds)
            ->whereDate('timestamp', now()->toDateString())
            ->get()
            ->groupBy('student_id');

        $result = [];

        $presentCount = 0;
        $lateComersCount = 0;
        foreach ($students as $s) {
            $logs = $attendances->get($s->id, collect());

            // first scan within check-in window
            $checkIn = $logs->first(function ($a) use ($checkinStart, $checkinEnd) {
                $t = Carbon::parse($a->timestamp);
                return $t->between($checkinStart, $checkinEnd);
            });

            // last scan within check-out window
            $checkOut = $logs->filter(function ($a) use ($checkoutStart, $checkoutEnd) {
                $t = Carbon::parse($a->timestamp);
                return $t->between($checkoutStart, $checkoutEnd);
            })->last();

            $result[] = [
                'student_id'   => $s->id,
                'registeration_id'      => $s->registeration_id ?? null,
                'name'         => $s->name ?? ($s->full_name ?? null),
                'guardian_name' => $s->guardian_name ?? null,
                'guardian_contact' => $s->guardian_contact ?? null,
                'checkin_at'   => $checkIn  ? Carbon::parse($checkIn->timestamp)->format('H:i')  : null,
                'checkout_at'  => $checkOut ? Carbon::parse($checkOut->timestamp)->format('H:i') : null,
                'late_comer' => !$checkIn && $logs->count(),
            ];

            $presentCount = $checkIn ? $presentCount+1 : $presentCount;
            $lateComersCount = !$checkIn && $logs->count() ? $lateComersCount+1 : $lateComersCount;
        }

        $standardName = '';
        if($standardId) {
            $standardName = Standard::where('id', $standardId)->value('name');
        }

        return [$result, $standardName, $presentCount, $lateComersCount];
    }

    public function getTotalStrength($schoolId, $standardId=null)
    {
        $q = Student::where('school_id', $schoolId);

        if ($standardId) {
            $q->where('standard_id', $standardId);
        }

        return $q->count();
    }
}