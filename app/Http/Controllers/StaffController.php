<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;

class StaffController extends Controller
{
    public function index()
    {
        $school_id = session('school_id');
        $staff = User::where([['school_id', '=', $school_id], ['type', '!=', 'admin']])->paginate(15);
        
        return view('staff.index', ['staffMembers' => $staff]);
    }

    public function store(Request $request)
    {
        $id = $request->id ? $request->id : null;

        $rules = [
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'type' => 'required|in:teacher,employee',
            'rfid' => 'required'
        ];
    
        // If no id is provided, or if password is provided, add password validation
        if (is_null($id) || $request->filled('password')) {
            $rules['password'] = 'required|min:8';
        }

        $validatedData = $request->validate($rules);

        // check if rfid is unique for current school
        $rfidExists = User::where([['rfid', '=', $validatedData['rfid']], ['school_id', '=', session('school_id')]])->first();
        if($rfidExists && $id != $rfidExists->id) {
            return redirect()->back()->with('error', 'RFID already taken');
        }

        // check if student with this rfid exists
        $studentExists = Student::where([['rfid', '=', $validatedData['rfid']], ['school_id', '=', session('school_id')]])->first();
        if($studentExists) {
            return redirect()->back()->with('error', 'RFID already taken');
        }

        $staffMember = $id ? User::where([['id', '=', $id]])->whereIn('type', ['teacher', 'employee'])->firstOrFail() : new User;

        if($id && $staffMember->school_id != session('school_id')) {
            return redirect()->route('staff.index')->with('error', 'Something went wrong!');
        }

        $staffMember->name = $validatedData['name'];
        $staffMember->email = $validatedData['email'];
        $staffMember->type = $validatedData['type'];
        $staffMember->rfid = $validatedData['rfid'];

        if ($request->filled('password')) {
            $staffMember->password = \Hash::make($request->password);
        }

        if (is_null($id)) {
            $staffMember->school_id = session('school_id');
        }

        $staffMember->save();

        $message = $id ? 'Staff Member updated successfully' : 'Staff Member created successfully';
        return redirect()->route('staff.index')->with('success', $message);
    }

    public function destroy($teacher_id)
    {
        $teacher = User::where('id',$teacher_id)->whereIn('type', ['teacher', 'employee'])->firstOrFail();
        if($teacher->school_id != session('school_id')) {
            return redirect()->route('staff.index')->with('error', 'Something went wrong!');
        }

        $teacher->standards()->detach();

        $teacher->delete();

        return redirect()->back()->with('success', 'Teacher deleted successfully');
    }
}
