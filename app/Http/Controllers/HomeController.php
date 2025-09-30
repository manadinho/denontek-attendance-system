<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use App\Models\Standard;
use App\Models\Device;
use App\Services\StandardService;

class HomeController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');
        $devices = Device::where('school_id', $school_id)->whereIn('type', ['registeration', 'attendance'])->get();

        $totalStrength = app(StandardService::class)->getTotalStrength($school_id);
        $todayAttendance = app(StandardService::class)->getStandardTodayAttendance($school_id);
        $presentStudents = $todayAttendance[2] ?? 0;
        $lateStudents = $todayAttendance[3] ?? 0;
        $absentStudents = $totalStrength - ($presentStudents + $lateStudents);

        return view('dashboard', ['devices' => $devices, 'totalStrength' => $totalStrength, 'presentStudents' => $presentStudents, 'lateStudents' => $lateStudents, 'absentStudents' => $absentStudents]);
    }

    public function standardsWithAttendnce()
    {
        $school_id = session('school_id');

        $standardQuery = Standard::where('school_id', $school_id);

        if(userType() == 'teacher') {
            $standardQuery->whereHas('teachers', function($query) {
                $query->where('user_id', user()->id);
            });
        }

        $standards = $standardQuery->get();

        $cards = "";
        foreach($standards as $standard) {
            $totalStrength = app(StandardService::class)->getTotalStrength($school_id, $standard->id);
            $todayAttendance = app(StandardService::class)->getStandardTodayAttendance($school_id, $standard->id);
            $presentStudents = $todayAttendance[2] ?? 0;
            $lateStudents = $todayAttendance[3] ?? 0;
            $absentStudents = $totalStrength - ($presentStudents + $lateStudents);
            $standard->total_strength = $totalStrength;
            $standard->present_students = $presentStudents;
            $standard->late_students = $lateStudents;
            $standard->absent_students = $absentStudents;
            $cards .= view('partials.attendance-card', ['standard' => $standard])->render();
        }

        return response()->json(['cards' => $cards]);
    }

    public function schools()
    {
        $schools = user()->load('schools')->schools;
        return view('schools', ['schools' => $schools]);
    }

    public function selectSchool($school_id)
    {
        $schools = user()->load('schools')->schools;
        
        if(!$schools->contains('id', $school_id)) {
            return redirect()->back();
        }

        session(['school_id' => $school_id]);
        $hub = Device::where('school_id', user()->school_id)->where('type', 'push_to_server')->first();
        if ($hub) {
            session(['hub_mac' => $hub->mac]);
        }
        
        return redirect()->route('dashboard');
    }

    public function removeAllSessions()
    {
        $this->abortIfNotSuperAdmin();

        Session::invalidate();
        Session::regenerateToken();

        $dir = storage_path('framework/sessions');

        foreach (File::files($dir) as $file) {
            if ($file->getFilename() === '.gitignore') continue;
            File::delete($file->getPathname());
        }
        
        return redirect()->route('login');
    }

    private function abortIfNotSuperAdmin()
    {
        if(userType() != 'superadmin') {
            abort(404);
        }
    }
}
