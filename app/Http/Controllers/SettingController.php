<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSetting;
use App\Models\Device;
use App\Services\RedisService;
use App\Services\SettingsService;

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
        $weekOffDays = explode(',', $schoolSettings->week_off_days);
        return view('school-settings.edit', ['schoolSettings' => $schoolSettings, 'regesterationDevices' => $regesterationDevices, 'attendanceDevices' => $attendanceDevices, 'weekOffDays' => $weekOffDays]);
    }

    public function updateSchoolSettings(Request $request)
    {
        $request->validate([
            'checkin_start' => 'required',
            'checkin_end' => 'required',
            'checkout_start' => 'required',
            'checkout_end' => 'required',
            'buffer_minutes' => 'required',
            'weekdays' => 'required|array',
            'admin_phone_numbers' => 'nullable|string',
        ]);

        $adminPhoneNumbers = null;
        if($request->admin_phone_numbers) {
            $validatedAdminPhoneNumbers = app(SettingsService::class)->validateAdminPhoneNumbers($request->admin_phone_numbers);
            if(!$validatedAdminPhoneNumbers['valid']) {
                return redirect()->back()->with('error', $validatedAdminPhoneNumbers['message'])->withInput();
            }

            $adminPhoneNumbers = $validatedAdminPhoneNumbers['phone_numbers'];
        }

        $weekOffDays = implode(',', $request->weekdays);

        $school_id = session('school_id');
        SchoolSetting::where('school_id', $school_id)->update(['checkin_start' => $request->checkin_start, 'checkin_end' => $request->checkin_end, 'checkout_start' => $request->checkout_start, 'checkout_end' => $request->checkout_end, 'week_off_days' => $weekOffDays, 'buffer_minutes' => $request->buffer_minutes, 'admin_phone_numbers' => $adminPhoneNumbers]);
        app(RedisService::class)->upsertSchool($school_id);
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
