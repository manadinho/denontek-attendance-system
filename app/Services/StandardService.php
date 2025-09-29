<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\Standard;
use App\Models\Student;
use Carbon\Carbon;

class StandardService
{
    public function getStandardTodayAttendance($standardId, $schoolId)
    {
        $settings = SchoolSetting::where('school_id', $schoolId)->first();
        if (!$settings) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'School settings not found.',
            ]);
        }

        $buffer = $settings->buffer_minutes ?? 0;

        // Build today's windows (no explicit TZ logic here)
        $today = Carbon::today(); // uses app timezone automatically
        $checkinStart   = Carbon::parse($today->toDateString().' '.$settings->checkin_start)->subMinutes($buffer);
        $checkinEnd     = Carbon::parse($today->toDateString().' '.$settings->checkin_end)->addMinutes($buffer);
        $checkoutStart  = Carbon::parse($today->toDateString().' '.$settings->checkout_start)->subMinutes($buffer);
        $checkoutEnd    = Carbon::parse($today->toDateString().' '.$settings->checkout_end)->addMinutes($buffer);

        // Students in this standard
        $students = Student::where([
            'school_id'   => $schoolId,
            'standard_id' => $standardId,
        ])->orderBy('created_at')->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $studentIds = $students->pluck('id');

        $attendances = Attendance::query()
            ->whereIn('student_id', $studentIds)
            ->whereDate('timestamp', now()->toDateString())
            ->get()
            ->groupBy('student_id');

        $result = [];

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
            ];
        }

        $standardName = Standard::where('id', $standardId)->value('name');

        return [$result, $standardName];
    }
}