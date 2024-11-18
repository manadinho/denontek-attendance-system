<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\Standard;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentReportsController extends Controller
{
    public function studentsDateWiseReport() 
    {
        $standards = Standard::where('school_id', session('school_id'))->get();
        $school_id = session('school_id');
        $schoolSettings = SchoolSetting::where('school_id', $school_id)->first();
        $weekOffDays = explode(',', $schoolSettings->week_off_days);
        return view("reports.student-date-wise-report", compact('standards', 'weekOffDays'));
    }

    public function studentsDateWiseReportGenerate(Request $request)
    {
        try {
            $request->validate([
                'from' => 'required|date',
                'to' => 'required|date',
                'students' => 'required|array',
            ]);

            // Parse the client-sent dates and adjust the time components
            $fromDate = Carbon::createFromFormat('m/d/Y', $request->from)->startOfDay(); // 2024-11-01 00:00:00
            $toDate = Carbon::createFromFormat('m/d/Y', $request->to)->endOfDay();       // 2024-11-30 23:59:59

            $data = [];

            // Loop through each student ID and retrieve data
            foreach ($request->students as $studentId) {
                // Get the student
                $student = Student::find($studentId);

                if (!$student) {
                    continue;
                }

                // Get attendance records for the student within the date range
                $attendanceRecords = Attendance::where('student_id', $student->id)
                    ->whereBetween('check_in', [$fromDate, $toDate])
                    ->get();

                // Initialize the attendance structure for the student
                $attendanceData = [];

                // Populate attendance data in the desired format
                foreach ($attendanceRecords as $att) {
                    $dateKey = Carbon::parse($att->check_in)->format('Y-m-d');
                    $attendanceData[$dateKey] = $att->check_in . '|' . ($att->check_out ?? '');
                }

                // Add the student and their attendance data to the main array
                $data[] = [
                    'student' => $student,
                    'attendance' => $attendanceData,
                ];
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

}
