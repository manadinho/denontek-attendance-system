<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index()
    {   
        $school_id = session('school_id');

        $timetables = Timetable::where([['school_id', '=', $school_id]])->paginate(15);
        
        return view('time-manage.index', compact('timetables'));
    }

    public function store() 
    {
        $data = request()->validate([
            'name' => 'required|string',
            'late_time' => 'required|integer',
            'leave_early_time' => 'required|integer',
            'on_time' => 'required|date_format:H:i',
            'off_time' => 'required|date_format:H:i',
        ]);

        $data['school_id'] = session('school_id');

        Timetable::updateOrCreate(['id' => request('id')], $data);

        $message = request('id') ? 'Timetable updated successfully' : 'Timetable created successfully';

        return back()->with('success', $message);
    }

    public function destroy($id)
    {
        $school_id = session('school_id');

        Timetable::where(['id' => $id, 'school_id' => $school_id])->delete();

        return back()->with('success', 'Timetable deleted successfully');
    }
}
