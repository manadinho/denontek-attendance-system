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
            $timeStamp = Carbon::createFromTimestamp($att['timestamp'])->subHours(5)->format('Y-m-d H:i:s');
            if($student) {
                $attendance[] = $this->checkIfAttendanceForStudent($device, $student, $timeStamp, $att['id']);
                continue;
            }

            $staff = User::where([['rfid', $att['rfid']], ['school_id', $device->school_id]])->first();
            if($staff) {
                $attendance[] = $this->checkIfAttendanceForStaff($device, $staff, $timeStamp, $att['id']);
                continue;
            }

            $attendance[] = ['message' => 'No record found'];
        }

        return response()->json($attendance);
    }

    private function checkIfAttendanceForStaff($device, $staff, $timestamp, $controllerId = null)
    {
        $recentAttendance = StaffAttendance::where('staff_id', $staff->id)
                            ->where('timestamp', '>=', Carbon::parse($timestamp)->subMinutes(20))
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
        $recentAttendance = Attendance::where('student_id', $student->id)
                            ->where('timestamp', '>=', Carbon::parse($timestamp)->subMinutes(20))
                            ->first();
        if($recentAttendance) {
            return ['message' => 'Multiple Swipes'];
        }

        Attendance::create([
            'controller_id' => $controllerId,
            'student_id' => $student->id,
            'school_id' => $device->school_id,
            'timestamp' => $timestamp,
        ]);

        return ['message' => 'Success'];
    }

    public function getLastAttId() 
    {
        $latestAttendance = Attendance::where([['school_id', auth()->user()->school_id]])->latest('controller_id')->first();
        $latestStaffAttendance = StaffAttendance::where([['school_id', auth()->user()->school_id]])->latest('controller_id')->first();
        
        $attendanceId = $latestAttendance->controller_id ?? 0;
        $staffAttendanceId = $latestStaffAttendance->controller_id ?? 0;

        $maxId = max($attendanceId, $staffAttendanceId);

        return response()->json(['id' => $maxId]);
    }
}
