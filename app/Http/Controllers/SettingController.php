<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSetting;
use App\Models\Device;

class SettingController extends Controller
{
    public function editSchoolSettings()
    {
        $school_id = session('school_id');
        $schoolSettings = SchoolSetting::where('school_id', $school_id)->first();
        if(!$schoolSettings) {
            $schoolSettings = new SchoolSetting();
            $schoolSettings->school_id = $school_id;
            $schoolSettings->save();
        }
        $schoolSettings = $schoolSettings ? $schoolSettings : new SchoolSetting();
        $devices = Device::where('school_id', $school_id)->whereIn('type', ['registeration', 'attendance'])->get();
        $regesterationDevices = $devices->where('type', 'registeration');
        $attendanceDevices = $devices->where('type', 'attendance');
        return view('school-settings.edit', ['schoolSettings' => $schoolSettings, 'regesterationDevices' => $regesterationDevices, 'attendanceDevices' => $attendanceDevices]);
    }

    public function updateSchoolSettings(Request $request)
    {
        $request->validate([
            'checkin_start' => 'required',
            'checkin_end' => 'required',
            'checkout_start' => 'required',
            'checkout_end' => 'required',
        ]);

        $school_id = session('school_id');
        SchoolSetting::where('school_id', $school_id)->update(['checkin_start' => $request->checkin_start, 'checkin_end' => $request->checkin_end, 'checkout_start' => $request->checkout_start, 'checkout_end' => $request->checkout_end]);
        return redirect()->route('school-settings.edit')->with('success', 'School settings updated successfully');
    }

    public function updateDevice(Request $request)
    {
        $request->validate([
            'deviceId' => 'required',
            'name' => 'required',
        ]);

        $school_id = session('school_id');
        Device::where('school_id', $school_id)->where('id', $request->deviceId)->update(['name' => $request->name]);
        return response()->json(['success' => true, 'message' => 'Device updated successfully']);
    }
}
