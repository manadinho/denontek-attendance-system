<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Standard;
use App\Models\User;
use App\Models\Device;

class StudentController extends Controller
{
    public function index()
    {
        $where = [['school_id', '=', session('school_id')]];
        $standrdFilter = request('standard');
        if($standrdFilter) {
            $where[] = ['standard_id', '=', $standrdFilter];
        }
        $students = Student::where($where)->with('standard', function($query) {$query->select('id', 'name');})->paginate(15);
        $standards = Standard::where('school_id', session('school_id'))->get(['id', 'name']);
        $registrationDevices = Device::where('school_id', session('school_id'))->where('type', 'registeration')->get();
        return view('students.index', ['students' => $students, 'standards' => $standards, 'registrationDevices' => $registrationDevices]);
    }

    public function store(Request $request)
    {
        $id = $request->id ? $request->id : null;

        $validatedData = $request->validate([
            'name' => 'required',
            'standard_id' => 'required',
            'guardian_name' => 'required',
            'guardian_contact' => 'required',
            'guardian_relation' => 'required',
            'rfid' => 'required'
        ]);

        // check if rfid is unique for current school
        $rfidExists = Student::where([['rfid', '=', $validatedData['rfid']], ['school_id', '=', session('school_id')]])->first();
        if($rfidExists && $id != $rfidExists->id) {
            return redirect()->back()->with('error', 'RFID already exists');
        }

        // check if staff member with this rfid exists
        $staffExists = User::where([['rfid', '=', $validatedData['rfid']], ['school_id', '=', session('school_id')]])->first();
        if($staffExists) {
            return redirect()->back()->with('error', 'RFID already exists');
        }

        $student = $id ? Student::where([['id', '=', $id]])->firstOrFail() : new Student;

        if($id && $student->school_id != session('school_id')) {
            return redirect()->route('students.index')->with('error', 'Something went wrong!');
        }

        $student->name = $validatedData['name'];
        $student->standard_id = $validatedData['standard_id'];
        $student->guardian_name = $validatedData['guardian_name'];
        $student->guardian_contact = $validatedData['guardian_contact'];
        $student->guardian_relation = $validatedData['guardian_relation'];
        $student->rfid = $validatedData['rfid'];
        $student->school_id = session('school_id');

        $student->save();

        $message = $id ? 'Student updated successfully' : 'Student created successfully';
        return redirect()->route('students.index')->with('success', $message);
    }

    public function destroy($teacher_id)
    {
        $student = Student::where('id', $teacher_id)->firstOrFail();
        if($student->school_id != session('school_id')) {
            return redirect()->route('teachers.index')->with('error', 'Something went wrong!');
        }

        $student->delete();

        return redirect()->back()->with('success', 'Student deleted successfully');
    }

    public function markManualAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'check_in' => 'required',
            'check_out' => 'required',
        ]);

        $student = Student::where([['id', '=', $request->student_id], ['school_id', '=', session('school_id')]])->first();
        if(!$student) {
            return redirect()->back()->with('error', 'Student not found');
        }

        $attendance = Attendance::where([['student_id', '=', $request->student_id], ['check_in', '=', $request->check_in]])->first();
        if($attendance) {
            $attendance->check_in = $request->check_in;
            $attendance->check_out = $request->check_out;
            $attendance->save();
        } else {
            Attendance::create([
                'student_id' => $request->student_id,
                'school_id' => session('school_id'),
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
            ]);
        }

        return redirect()->back()->with('success', 'Attendance marked successfully');
    }

    public function getStudentsByStandard(Request $request)
    {
        try {
            $request->validate([
                'standard_id' => 'required',
            ]);
    
            $students = Student::where([['standard_id', '=', $request->standard_id], ['school_id', '=', session('school_id')]])->get(['id', 'name']);
            return response()->json(['success' => true, 'students' => $students]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
