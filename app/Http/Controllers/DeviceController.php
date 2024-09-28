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
        ]);

        $device = Device::where([['mac_address', '=', $request->mac_address]])->first();
        // $device = Device::where([['mac_address', '=', "3C:61:05:14:BA:80"]])->first();

        if(!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $student = Student::where([['rfid', '=', $request->rfid], ['school_id', '=', $device->school_id]])->first();
        if($student) {
            return response()->json($this->checkIfAttendanceForStudent($device, $student));
        }
        
        $staff = User::where([['rfid', '=', $request->rfid], ['school_id', '=', $device->school_id]])->first();
        if($staff) {
            return response()->json($this->checkIfAttendanceForStaff($device, $staff));
        }

        return response()->json(['message' => 'No record found'], 404);
    }

    private function checkIfAttendanceForStaff($device, $staff)
    {
        $recentAttendance = StaffAttendance::where('staff_id', $staff->id)
                            ->where('created_at', '>=', Carbon::now()->subMinutes(20))
                            ->first();
        if($recentAttendance) {
            return ['message' => 'Multiple Swipes'];
        }

        StaffAttendance::create([
            'staff_id' => $staff->id,
            'school_id' => $device->school_id,
            'timestamp' => Carbon::now(),
        ]);

        return ['message' => 'Success'];
    }

    private function checkIfAttendanceForStudent($device, $student)
    {
        $schoolSetting = SchoolSetting::where('school_id', $device->school_id)->first();
        $todayStudentAttendance = null;
        
        $todayStudentAttendance = Attendance::where('student_id', $student->id)->whereDate('created_at', Carbon::today())->first();

        if(date('H:i:s') >= $schoolSetting->checkin_start && date('H:i:s') <= $schoolSetting->checkin_end) {
            if(!$todayStudentAttendance) {
                Attendance::create([
                    'student_id' => $student->id,
                    'school_id' => $device->school_id,
                    'check_in' => Carbon::now(),
                ]);
                return ['message' => 'Check in success'];
            }
            return ['message' => 'Already checked in'];
        }

        if(date('H:i:s') >= $schoolSetting->checkout_start && date('H:i:s') <= $schoolSetting->checkout_end) {
            if($todayStudentAttendance) {
                $todayStudentAttendance->update([
                    'check_out' => Carbon::now(),
                ]);
                return ['message' => 'Check out success'];
            }
            return ['message' => 'Check in first'];
        }

        // $message = $todayStudentAttendance ? 'Check out time not started' : 'Check in time not started';

        // $message = date('H:i:s') > $schoolSetting->checkin_end ? 'Check in time ended' : $message;
        // $message = date('H:i:s') > $schoolSetting->checkout_end ? 'Check out time ended' : $message;
        $current_time = date('H:i:s');
        $message = '';

        if ($todayStudentAttendance) {
            if ($current_time > $schoolSetting->checkout_end) {
                $message = 'Check out time ended';
            } else {
                $message = 'Check out time not started';
            }
        } else {
            if ($current_time > $schoolSetting->checkin_end) {
                $message = 'Check in time ended';
            } else {
                $message = 'Check in time not started';
            }
        }

        return ['message' => $message];
    }
}
