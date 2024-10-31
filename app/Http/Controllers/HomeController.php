<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Standard;
use App\Models\Device;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');
        $devices = Device::where('school_id', $school_id)->whereIn('type', ['registeration', 'attendance'])->get();

        return view('dashboard', ['devices' => $devices]);
    }

    public function standardsWithAttendnce()
    {
        $school_id = session('school_id');
        $standardQuery = Standard::withCount('students')
                        ->withCount(['students as present_students_count' => function($query) {
                            $query->whereHas('attendances', function($query) {
                                $query->whereDate('check_in', Carbon::today());
                            });
                        }])
                        ->where('school_id', $school_id);

        if(userType() == 'teacher') {
            $standardQuery->whereHas('teachers', function($query) {
                $query->where('user_id', user()->id);
            });
        }

        $standards = $standardQuery->get();

        $cards = "";
        foreach($standards as $standard) {
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
        return redirect()->route('dashboard');
    }
}
