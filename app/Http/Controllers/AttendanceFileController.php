<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSync;
use App\Models\Device;
use App\Models\SchoolSetting;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\User;
use App\Services\WebsocketService;
use Carbon\Carbon;

class AttendanceFileController extends Controller
{
    public function index($deviceId=null)
    {
        $devices = Device::where('school_id', session('school_id'))->get();

        if(!$deviceId) {
            return redirect()->route('attendance-sync.index', $devices->first()->id);
        }

        $selectedDevice = $devices->where('id', $deviceId)->first();

        $attendanceFiles = AttendanceSync::where('device_id', $selectedDevice->id)
                            ->orderBy('created_at', 'desc')
                            ->whereDate('date', '>=', now()->subDays(30))
                            ->get();
        $selectedDevice->attendance_files = $attendanceFiles;

        return view('attendance.index', ['devices' => $devices, 'selectedDevice' => $selectedDevice]);
    }

    public function syncAttendance($macAddress, $fileName)
    {
        info ("Attendance file sync hit for MAC: $macAddress, File: $fileName");
        info('Request Data: '.json_encode(request()->getContent()));

        $device = Device::with('school')->where('mac_address', $macAddress)->first();
        if(!$device) {
            info("Device with MAC: $macAddress not found.");
            return response()->json(['status' => 'error', 'message' => 'Device not found'], 404);
        }

        $raw = request()->getContent();

        // Step 1: normalize line endings to "\n"
        $normalized = str_replace(["\r\n", "\r"], "\n", $raw);

        // Step 2: split into lines
        $lines = array_filter(explode("\n", $normalized)); // removes empty lines

        // Step 3: first line is header, remove it
        $header = array_shift($lines); // "rfid,timestamp"

        // Step 4: parse each remaining line into [rfid, timestamp]
        $records = [];

        foreach ($lines as $line) {
            $parts = str_getcsv($line); // splits by comma safely
            // expect exactly 2 columns: [0]=rfid, [1]=timestamp
            if (count($parts) === 2) {
                $rfid = trim($parts[0]);
                $ts   = trim($parts[1]);

                // ignore bad/empty rows (just in case)
                if ($rfid !== '' && $ts !== '') {
                    $records[] = [
                        'rfid'       => $rfid,
                        'timestamp'  => $ts,
                    ];
                }
            }
        }

        // $records now has all rows as associative arrays
        info('Parsed Records: ' . json_encode($records, JSON_PRETTY_PRINT));
        foreach($records as $record) {
            $student = Student::where([['rfid', $record['rfid']], ['school_id', $device->school_id]])->first();
            $timeStamp = Carbon::createFromTimestamp($record['timestamp'])->subHours(5)->format('Y-m-d H:i:s');
            if($student) {
                $attendance[] = $this->checkIfAttendanceForStudent($device, $student, $timeStamp, $device->id);
                continue;
            }

            $staff = User::where([['rfid', $record['rfid']], ['school_id', $device->school_id]])->first();
            if($staff) {
                $attendance[] = $this->checkIfAttendanceForStaff($device, $staff, $timeStamp, $device->id);
                continue;
            }

            $attendance[] = ['message' => 'No record found'];
        }
        AttendanceSync::where('device_id', $device->id)->where('name', $fileName)->update(['synced' => true]);

        // get one file for this device id having sync as 0 in last 30 days
        $missingFile = AttendanceSync::where('device_id', $device->id)
                            ->where('synced', false)
                            ->where('created_at', '>=', Carbon::now()->subDays(30))
                            ->first();

        $missingFileName = $missingFile ? $missingFile->name : '';

        if(!$missingFileName) {
            $deviceId = $device->id;
            app(WebsocketService::class)->broadcastMessage($device->school->channel_id, 'ATTENDANCE_SYNC_COMPLETE', $deviceId);
        }
    
        return response()->json(['status' => 'success', 'missing_file' => $missingFileName, 'message' => 'File uploaded successfully']);
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

    public function runAttendanceSyncCron()
    {
        $now = Carbon::now();

        info('got hit.........runAttendanceSyncCron');

        $roundedTime = $this->roundToNearest30($now);

        $schoolSettings = SchoolSetting::where('checkin_sync_time', $roundedTime)->orWhere('checkout_sync_time', $roundedTime)->with('school')->get();

        foreach ($schoolSettings as $schoolSetting) {
            $school = $schoolSetting->school;

            $devices = $school->devices()->get();

            $fileName = '';

            if($schoolSetting->checkin_sync_time == $roundedTime) {
                $fileName = 'morCheckin' . $now->day . '.csv';
            } else {
                $fileName = 'morCheckout' . $now->day . '.csv';
            }

            foreach ($devices as $device) {
                AttendanceSync::create(
                    [
                        'school_id' => $school->id,
                        'device_id' => $device->id,
                        'name' => $fileName,
                        'type' => $schoolSetting->checkin_sync_time == $roundedTime ? 'Checkin':"Checkout",
                        'date' => $now->format('Y-m-d'),
                    ]
                );
            }

            app(WebsocketService::class)->broadcastMessage($school->channel_id, 'message', "SYNC_ATTENDANCE|$fileName");
        }
    }
    
    private function roundToNearest30(Carbon $t)
    {
        $m = $t->minute;
        $rounded = (int) round($m / 30) * 30;
        $t = $t->copy()->second(0);
        if ($rounded === 60) {
            return $t->addHour()->minute(0);
        }
        return $t->minute($rounded)->format("H:i") . ":00";
    }
}
