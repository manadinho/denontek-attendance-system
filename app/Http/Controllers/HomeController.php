<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Standard;
use App\Models\Device;
use App\Models\SchoolSetting;
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

        $schoolSettings = SchoolSetting::where('school_id', $school_id)->first();

        $buffer = (int)($schoolSettings->buffer_minutes ?? 0);

        $today = Carbon::today();
        $checkinStart = Carbon::parse($today->toDateString() . ' ' . $schoolSettings->checkin_start)->subMinutes($buffer);
        $checkinEnd   = Carbon::parse($today->toDateString() . ' ' . $schoolSettings->checkin_end)->addMinutes($buffer);

        $standardQuery = Standard::withCount('students')
                        ->withCount(['students as present_students_count' => function($query) use($checkinStart, $checkinEnd) {
                            $query->whereHas('attendances', function($a) use($checkinStart, $checkinEnd) {
                                $a->whereBetween('timestamp', [$checkinStart, $checkinEnd]);
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
