<?php

namespace App\Services;

use App\Models\AdminAlert;
use App\Models\School;
use Illuminate\Support\Facades\Http;

class AdminAlertService
{
    public function updateAdminAlert($request)
    {
        $adminAlert = AdminAlert::find($request->id);

        if ($adminAlert->school_id != session('school_id')) {
            return false;
        }

        $contacts = json_encode($request->input('admin_contacts', []));

        return $adminAlert->update([
                'time'           => $request->time,
                'admin_contacts' => $contacts,
                'active'         => $request->active ? true : false,
            ]);
    }

    public function sendAdminAlert($alert)
    {
        switch ($alert->title) {
            case 'Daily Attendance Summary':
                $this->sendDailyAttendanceAdminAlert($alert);
                break;
            
            default:
                break;
        }
    }

    public function sendDailyAttendanceAdminAlert($alert)
    {
        // check if today is holiday for the school then return
        if(app(SettingsService::class)->isTodayHoliday($alert->school_id)) {
            return;
        }
        
        $totalStrength = app(StandardService::class)->getTotalStrength($alert->school_id);
        $result = app(StandardService::class)->getStandardTodayAttendance($alert->school_id);
        $school = School::with('schoolSetting')->where('id', $alert->school_id)->first();
        $absent = $totalStrength - ($result[2] + $result[3]);

        $message = '';
        $checkinStart = $school->schoolSetting->checkin_start ? substr($school->schoolSetting->checkin_start, 0, 5) : '';
        $checkinEnd = $school->schoolSetting->checkin_end ? substr($school->schoolSetting->checkin_end, 0, 5) : '';
        if($result) {
            $message = "📝 *Daily Attendance – {$school->name}*\n\nTotal Strength: {$totalStrength}\nPresent: {$result[2]}\nLate: {$result[3]}\nAbsent: {$absent}\nWindow: {$checkinStart}–{$checkinEnd}";
        }

        if(!$message) {
            return;
        }

        foreach (json_decode($alert->admin_contacts) as $contact) {
            if(!$contact) {
                continue;
            }

            Http::post(env('WHATSAPP_URL') . '/send', [
                'number'      => $contact,
                'message' => $message,
            ]);    
        }
    }
}