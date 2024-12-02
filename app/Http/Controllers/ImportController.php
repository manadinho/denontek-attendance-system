<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\Request;
use App\Models\FileUpload;
use App\Models\School;
use App\Models\FileUploadFail;
use App\Imports\StudentFileImport;
use App\Imports\StaffFileImport;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index()
    {
        $this->abortIfNotSuperAdmin();
        $schools = School::select('id', 'name')->get();
        $fileUploads = FileUpload::with('school')->latest()->paginate(10);
        return view('imports.index', ['schools' => $schools, 'fileUploads' => $fileUploads]);
    }

    public function import(FileUploadRequest $request)
    {
        $this->abortIfNotSuperAdmin();
        // upload file to public directory folder named 'fileUploads'
        $file = $request->file('file');
        $file_name = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('fileUploads'), $file_name);

        $fileUpload = FileUpload::create([
                'school_id' => $request->school_id,
                'file_name' => $file_name,
                'total_records' => 0,
                'good' => 0,
                'cannot_upload' => 0,
                'type' => $request->type
            ]);

        // import uploaded file
        DB::transaction(function() use($fileUpload, $request){
            try {
                $studentFileImport = $request->type == 'student' ? new StudentFileImport($fileUpload) : new StaffFileImport($fileUpload);
                $studentFileImport->import('fileUploads/' . $fileUpload->file_name);   
            } catch (\Throwable $th) {
                $fileUpload->update(['status' => 'failed']);
            }
        });

        return redirect()->route('imports.index')->with('success', 'File uploaded successfully');
    }

    public function showFailed($uploadId)
    {
        $this->abortIfNotSuperAdmin();
        $fileUpload = FileUpload::where('id', $uploadId)->with('fileUploadFails')->first();
        $columns = config('fileuploadtypes.'.$fileUpload->type);
        return view('imports.show-failed', ['uploadFails' => $fileUpload->fileUploadFails, 'columns' => $columns]);
    }

    private function abortIfNotSuperAdmin()
    {
        if(userType() != 'superadmin') {
            abort(404);
        }
    }
}
