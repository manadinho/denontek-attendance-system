<?php

namespace App\Http\Controllers;

use App\Events\RegisterStudentEvent;
use App\Models\Device;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\StaffAttendance;
use App\Models\SchoolSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function registerRfid(Request $request)
    {
        $request->validate([
            'mac_address' => 'required',
            'rfid' => 'required',
        ]);

        $device = Device::where([['mac_address', '=', $request->mac_address]])->first();
        // $device = Device::where([['mac_address', '=', "3C:61:05:14:BA:80"]])->first();

        if(!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        RegisterStudentEvent::dispatch($device->school_id, $request->rfid);

        return response()->json(['success' => true]);
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'mac_address' => 'required',
            'rfid' => 'required',
            'timestamp' => 'required'
        ]);

        $device = Device::where([['mac_address', '=', $request->mac_address]])->first();
        // $device = Device::where([['mac_address', '=', "3C:61:05:14:BA:80"]])->first();

        if(!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $student = Student::where([['rfid', '=', $request->rfid], ['school_id', '=', $device->school_id]])->first();
        if($student) {
            return response()->json($this->checkIfAttendanceForStudent($device, $student, $request->timestamp));
        }
        
        $staff = User::where([['rfid', '=', $request->rfid], ['school_id', '=', $device->school_id]])->first();
        if($staff) {
            return response()->json($this->checkIfAttendanceForStaff($device, $staff, $request->timestamp));
        }

        return response()->json(['message' => 'No record found'], 404);
    }

    public function markAttendanceBulk(Request $request)
    {

        $device = Device::where([['school_id', auth()->user()->school_id]])->first();

        foreach($request->attendance as $att) {
            $student = Student::where([['rfid', $att['rfid']], ['school_id', $device->school_id]])->first();
            if($student) {
                $attendance[] = $this->checkIfAttendanceForStudent($device, $student, $att['timestamp'], $att['id']);
                continue;
            }

            $staff = User::where([['rfid', $att['rfid']], ['school_id', $device->school_id]])->first();
            if($staff) {
                $attendance[] = $this->checkIfAttendanceForStaff($device, $staff, $att['timestamp'], $att['id']);
                continue;
            }

            $attendance[] = ['message' => 'No record found'];
        }

        return response()->json($attendance);
    }

    private function checkIfAttendanceForStaff($device, $staff, $timestamp, $controllerId = null)
    {
        $recentAttendance = StaffAttendance::where('staff_id', $staff->id)
                            ->where('created_at', '>=', Carbon::now()->subMinutes(20))
                            ->first();
        if($recentAttendance) {
            return ['message' => 'Multiple Swipes'];
        }

        StaffAttendance::create([
            'controller_id' => $controllerId,
            'staff_id' => $staff->id,
            'school_id' => $device->school_id,
            'timestamp' => $timestamp,
        ]);

        return ['message' => 'Success'];
    }

    private function checkIfAttendanceForStudent($device, $student, $timestamp, $controllerId = null)
    {
        // Convert the provided timestamp to a Carbon instance
        $providedTime = Carbon::parse($timestamp);
        
        $schoolSetting = SchoolSetting::where('school_id', $device->school_id)->first();
        $todayStudentAttendance = null;

        // Use the provided timestamp date instead of today's date
        $todayStudentAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('check_in', $providedTime->toDateString())
            ->first();

        // Use the time from the provided timestamp
        $currentTime = $providedTime->format('H:i:s');

        // Check if current time is within check-in time
        if($currentTime >= $schoolSetting->checkin_start && $currentTime <= $schoolSetting->checkin_end) {
            if(!$todayStudentAttendance) {
                Attendance::create([
                    'controller_id' => $controllerId,
                    'student_id' => $student->id,
                    'school_id' => $device->school_id,
                    'check_in' => $providedTime,  // Use provided timestamp for check-in time
                ]);
                return ['message' => 'Check in success'];
            }
            return ['message' => 'Already checked in'];
        }

        // Check if current time is within check-out time
        if($currentTime >= $schoolSetting->checkout_start && $currentTime <= $schoolSetting->checkout_end) {
            if($todayStudentAttendance) {
                $todayStudentAttendance->update([
                    'check_out' => $providedTime,  // Use provided timestamp for check-out time
                ]);
                return ['message' => 'Check out success'];
            }
            return ['message' => 'Check in first'];
        }

        // Determine appropriate message for the current time
        $message = '';

        if ($todayStudentAttendance) {
            if ($currentTime > $schoolSetting->checkout_end) {
                $message = 'Check out time ended';
            } else {
                $message = 'Check out time not started';
            }
        } else {
            if ($currentTime > $schoolSetting->checkin_end) {
                $message = 'Check in time ended';
            } else {
                $message = 'Check in time not started';
            }
        }

        return ['message' => $message];
    }
}
