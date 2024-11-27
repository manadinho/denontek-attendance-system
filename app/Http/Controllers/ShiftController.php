<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Timetable;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');

        $shifts = Shift::where([['school_id', '=', $school_id]])->paginate(15);

        $timetables = Timetable::where('school_id', $school_id)->get();

        return view('shifts.index', compact('shifts', 'timetables'));
    }

    public function store() 
    {
        $data = request()->validate([
            'name' => 'required|string'
        ]);

        $data['school_id'] = session('school_id');

        // if shift_id is present, create default timetable for shift with null values

        if(!request('id')) {
            $timetables = [
                'MONDAY' => null,
                'TUESDAY' => null,
                'WEDNESDAY' => null,
                'THURSDAY' => null,
                'FRIDAY' => null,
                'SATURDAY' => null,
                'SUNDAY' => null,
            ];

            $data['timetables'] = json_encode($timetables);
        }

        Shift::updateOrCreate(['id' => request('id')], $data);

        $message = request('id') ? 'Shift updated successfully' : 'Shift created successfully';

        return back()->with('success', $message);
    }

    public function destroy($id)
    {
        // TODO: need to add check if this shift is added to any user.
        $school_id = session('school_id');

        Shift::where(['id' => $id, 'school_id' => $school_id])->delete();

        return back()->with('success', 'Shift deleted successfully');
    }

    public function addTimetables()
    {
        $data = request()->validate([
            'id' => 'required|integer',
            'timetables' => 'required'
        ]);

        // if data is not validated
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Invalid data']);
        }

        $shift = Shift::find($data['id']);

        // if shift not found
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found']);
        }


        $shift->timetables = json_encode($data['timetables']);
        $shift->save();

        return response()->json(['success' => true, 'message' => 'Timetable added successfully']);
    }
}
