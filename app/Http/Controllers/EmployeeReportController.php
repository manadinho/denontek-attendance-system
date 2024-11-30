<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\StaffAttendance;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Time;

class EmployeeReportController extends Controller
{
    public function checkinCheckoutReport()
    {
        $school_id = session('school_id');

        $employees = User::where('school_id', $school_id)->get();

        $schoolSettings = SchoolSetting::where('school_id', $school_id)->first();

        $weekOffDays = explode(',', $schoolSettings->week_off_days);

        return view('reports.employee-checkin-checkout-report', compact('employees', 'weekOffDays'));
    }

    public function checkinCheckoutReportGenerate(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
            'employees' => 'required|array',
        ]);

        $school_id = session('school_id');

        // Parse the client-sent dates and adjust the time components
        $fromDate = Carbon::createFromFormat('m/d/Y', $request->from)->startOfDay(); // 2024-11-01 00:00:00
        $toDate = Carbon::createFromFormat('m/d/Y', $request->to)->endOfDay();       // 2024-11-30 23:59:59

        [$days, $dates] = $this->generateDatesAndDays($fromDate, $toDate);

        // if user trying to get report greater than 31 days
        if (count($dates) > 31) {
            return response()->json([
                'success' => false,
                'message' => 'You can only generate report for 31 days at a time.'
            ]);
        }

        $employees = User::where('school_id', $school_id)->whereIn('id', $request->employees)->get();

        $attendance = $this->getAttendance($request->employees, $dates);

        $timetables = Timetable::where('school_id', $school_id)->get();

        $schoolSettings = SchoolSetting::where('school_id', $school_id)->first();

        // loop through dates and employees to create a 2D array
        $attendanceArray = [];
        foreach ($dates as $index => $date) {
            foreach ($employees as $employee) {
                $attendanceEntries = $attendance->filter(function ($item) use ($date, $employee) {
                    return $item->staff_id == $employee->id && Carbon::parse($item->timestamp)->toDateString() === $date;
                });



                $checkin = null;
                $checkout = null;

                if($attendanceEntries->count() > 0) {
                    // now we have multip entries for a single employee on a single date so we need to get first and last entry
                    $checkin = $attendanceEntries->first()->timestamp;

                    if($attendanceEntries->count() > 1) {
                        $checkout = $attendanceEntries->last()->timestamp;
                    }
                }

                $shift_user = DB::table('shift_user')
                    ->where('user_id', $employee->id)
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->first();

                if($shift_user) {
                    $shift = DB::table('shifts')
                        ->where('id', $shift_user->shift_id)
                        ->first();
                    
                    $shiftTimetables = json_decode($shift->timetables, true);

                    $timetable = $timetables->where('id', $shiftTimetables[$days[$index]])->first();
                    $result = [
                        'checkin_status' => 'N/A',
                        'checkout_status' => 'N/A',
                    ];

                    if($timetable) {
                        $onTimeCarbon = Carbon::parse($timetable->on_time);
                        $offTimeCarbon = Carbon::parse($timetable->off_time);

                        // Initialize result variables
                        $isLate = false;
                        $leftEarly = false;
                        $result = [];

                        // Check-in logic (optional)
                        if ($checkin) {
                            $checkinTime = Carbon::parse($checkin);

                            // Check if the user is late
                            if ($checkinTime->diffInMinutes($onTimeCarbon, false) > $timetable->late_time) {
                                $isLate = true;
                                $result['checkin_status'] = 'Late';
                            } else {
                                $result['checkin_status'] = 'On Time';
                            }
                        } else {
                            $result['checkin_status'] = 'Did Not Punch-In';
                        }

                        // Check-out logic (optional)
                        if ($checkout) {
                            $checkoutTime = Carbon::parse($checkout);
                            if ($offTimeCarbon->diffInMinutes($checkoutTime, false) > $timetable->leave_early_time) {
                                $leftEarly = true;
                                $result['checkout_status'] = 'Left Early';
                            } else {
                                $result['checkout_status'] = 'Completed Schedule';
                            }
                        } else {
                            $result['checkout_status'] = 'Did Not Punch-Out';
                        }
                    }
                } else {
                    $shift = null;
                }

                $weekOffDays = explode(',', $schoolSettings->week_off_days);

                $attendanceArray[] = [
                    'day_of_week' => (in_array($days[$index], $weekOffDays)) ? true : false,
                    'date' => $date,
                    'day' => $days[$index],
                    'employeeId' => $employee->id,
                    'employee' => $employee->name,
                    'shift' => $shift ? $shift->name : 'N/A',
                    'timetable' => $timetable ? $timetable->name : 'N/A',
                    'on_time' => $timetable ? $timetable->on_time : 'N/A',
                    'off_time' => $timetable ? $timetable->off_time : 'N/A',
                    'checkin' => $checkin ? Carbon::parse($checkin)->format('H:i:s') : null,
                    'checkout' => $checkout ? Carbon::parse($checkout)->format('H:i:s') : null,
                    'checkin_status' => $result['checkin_status'],
                    'checkout_status' => $result['checkout_status']
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $attendanceArray
        ]);

    }

    private function generateDatesAndDays($startDate, $endDate) {
    
        $daysArray = [];
        $datesArray = [];
    
        // Loop through each date from start to end
        while ($startDate <= $endDate) {
            // Format the date as YYYY-MM-DD
            $formattedDate = $startDate->format('Y-m-d');
            // Get the day of the week in uppercase
            $day = strtoupper($startDate->format('l'));
    
            $daysArray[] = $day;
            $datesArray[] = $formattedDate;
    
            // Move to the next day
            $startDate->modify('+1 day');
        }
    
        // Assign the arrays to variables (similar to global variables in JS)
        $GLOBALS['DAYS'] = $daysArray;
        $GLOBALS['DATES'] = $datesArray;
    
        return [
            $daysArray,
            $datesArray
        ];
    }

    private function getAttendance($employeeIds, $dates) {
        $query = StaffAttendance::whereIn('staff_id', $employeeIds)
            ->where(function ($q) use ($dates) {
                foreach ($dates as $date) {
                    $q->orWhereRaw('DATE(`timestamp`) = ?', [$date]);
                }
            });

        return $query->get();
    }
}
