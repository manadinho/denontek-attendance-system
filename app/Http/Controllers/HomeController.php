<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use App\Models\Standard;
use App\Models\Device;
use App\Models\Owner;
use App\Models\School;
use App\Models\User;
use App\Services\StandardService;
use Illuminate\Support\Str;
use DB;

class HomeController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');
        $devices = Device::where('school_id', $school_id)->get();
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
        if(userType() == 'superadmin') {
            $schools = School::all();
        } else {
            $schools = user()->load('schools')->schools;
        }

        return view('schools', ['schools' => $schools]);
    }

    public function selectSchool($school_id)
    {
        if(userType() != 'superadmin') {
            $schools = user()->load('schools')->schools;
        
            if(!$schools->contains('id', $school_id)) {
                return redirect()->back();
            }
        }

        session(['school_id' => $school_id]);
        session(['channel_id' => School::where('id', $school_id)->value('channel_id')]);
        
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

    public function setupSchool()
    {
        $this->abortIfNotSuperAdmin();

        return view('setup-school');
    }

    public function setupSchoolSave()
    {
        $this->abortIfNotSuperAdmin();

        $request = request();
        $data = $request->validate([
            // School
            'school.name'            => ['required','string','max:255'],
            'school.address'         => ['required','string','max:500'],

            // Settings
            'settings.checkin_start'     => ['required','date_format:H:i'],
            'settings.checkin_end'       => ['required','date_format:H:i','after:settings.checkin_start'],
            'settings.checkout_start'    => ['required','date_format:H:i'],
            'settings.checkout_end'      => ['required','date_format:H:i','after:settings.checkout_start'],
            'settings.buffer_minutes'    => ['required','integer','min:0'],
            'settings.checkin_sync_time' => ['required','date_format:H:i'],
            'settings.checkout_sync_time'=> ['required','date_format:H:i'],
            'settings.whatsapp_url'      => ['required','url'],

            // Owner
            'owner.name'             => ['required','string','max:255'],
            'owner.email'            => ['required','email','max:255'],
            'owner.password'         => ['required','string','min:6'],

            // Admin user
            'admin.name'             => ['required','string','max:255'],
            'admin.email'            => ['required','email','max:255'],
            'admin.contact'          => ['nullable','string','max:30'],
            'admin.password'         => ['required','string','min:6'],

            // Device (optional)
            'device.name'            => ['nullable','string','max:255'],
            'device.mac_address'     => ['nullable','string','max:32'],

            // Admin alert (optional)
            'alert.title'            => ['nullable','string','max:255'],
            'alert.time'             => ['nullable','date_format:H:i'],
            'alert.admin_contacts'   => ['nullable','string'], // comma/line separated
            'alert.active'           => ['nullable','boolean'],
        ]);

        // dd($data);

        return DB::transaction(function () use ($data) {
            // 1) School
            $school = School::create([
                'name'       => $data['school']['name'],
                'address'    => $data['school']['address'],
                'channel_id' => Str::upper(Str::random(7)), // temp, finalized after we get ID
            ]);

            $school->channel_id = $school->channel_id.'-'.$school->id;
            $school->save();

            // 2) Settings
            DB::table('school_settings')->updateOrInsert(
                ['school_id' => $school->id],
                [
                    'school_id'           => $school->id,
                    'checkin_start'       => $data['settings']['checkin_start'],
                    'checkin_end'         => $data['settings']['checkin_end'],
                    'checkout_start'      => $data['settings']['checkout_start'],
                    'checkout_end'        => $data['settings']['checkout_end'],
                    'buffer_minutes'      => $data['settings']['buffer_minutes'],
                    'checkin_sync_time'   => $data['settings']['checkin_sync_time'],
                    'checkout_sync_time'  => $data['settings']['checkout_sync_time'],
                    'whatsapp_url'        => $data['settings']['whatsapp_url'],
                ]
            );

            // 3) Owner + pivot
            $owner = Owner::updateOrCreate(
                ['email' => $data['owner']['email']],
                [
                    'name'     => $data['owner']['name'],
                    'email'    => $data['owner']['email'],
                    'password' => \Hash::make($data['owner']['password']),
                ]
            );
            DB::table('owner_school')->updateOrInsert(
                ['owner_id' => $owner->id, 'school_id' => $school->id],
                ['owner_id' => $owner->id, 'school_id' => $school->id]
            );

            // 4) Admin user
            User::updateOrCreate(
                ['email' => $data['admin']['email']],
                [
                    'school_id' => $school->id,
                    'name'      => $data['admin']['name'],
                    'email'     => $data['admin']['email'],
                    'contact'   => $data['admin']['contact'] ?? null,
                    'password'  => \Hash::make($data['admin']['password']),
                    'type'      => 'admin',
                ]
            );

            // 5) Device
            if (!empty($data['device']['name']) || !empty($data['device']['mac_address'])) {
                DB::table('devices')->insert([
                    'school_id'   => $school->id,
                    'name'        => $data['device']['name'] ?? 'Device 1',
                    'mac_address' => $data['device']['mac_address'] ?? null,
                    'created_at'  => now(),
                ]);
            }

            $alertData = [
                'school_id' => $school->id,
                'title'      => 'Daily Attendance Summary',
                'time'   => '11:00',
                'admin_contacts' => json_encode([]),
                'active'   => false,
                'created_at' => now(),
            ];

            DB::table('admin_alerts')->insert($alertData);

            return redirect()
                ->back()
                ->with('status', 'School created: '.$school->name.' (Channel: '.$school->channel_id.')');
        });
    }

    private function abortIfNotSuperAdmin()
    {
        if(userType() != 'superadmin') {
            abort(404);
        }
    }
}
