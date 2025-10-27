<?php

// app/Http/Controllers/AttendanceMessageTemplateController.php
namespace App\Http\Controllers;

use App\Models\AttendanceMessageTemplate;
use App\Services\AttendanceMessageRenderer;
use App\Services\RedisService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceMessageTemplateController extends Controller
{
    public function index(Request $req){
        $school_id = session('school_id');
        $templates = AttendanceMessageTemplate::where('school_id',$school_id)
            ->orderBy('type')->orderByDesc('is_active')->latest()->paginate(20);

        return view('attendance_templates.index', compact('templates'));
    }

    public function create(){
        return view('attendance_templates.create');
    }

    public function store(Request $req){
        $data = $req->validate([
            'type'    => ['required', Rule::in(['arrival','departure'])],
            'body'    => ['required','string','max:500'], // SMS/WA length guard
            'is_active' => ['sometimes','boolean'],
        ]);

        $data['school_id'] = session('school_id');
        $tpl = AttendanceMessageTemplate::create($data);

        if ($tpl->is_active) {
            // ensure only one active per (school,type,channel,locale)
            AttendanceMessageTemplate::where('school_id',$data['school_id'])
                ->where('type',$tpl->type)
                ->where('id','!=',$tpl->id)
                ->where('type', $tpl->type)
                ->update(['is_active'=>false]);
        }

        // get all templates for this school
        // and push to redis
        $templates = AttendanceMessageTemplate::where(['school_id' => $data['school_id'], 'is_active' => true])->get();
        app(RedisService::class)->upsertMessageTemplates($templates, session('channel_id'));

        return redirect()->route('attendance-templates.index')->with('ok','Template saved.');
    }

    public function edit(AttendanceMessageTemplate $attendance_template){
        return view('attendance_templates.edit', ['tpl'=>$attendance_template]);
    }

    public function update(Request $req, AttendanceMessageTemplate $attendance_template){

        $data = $req->validate([
            'type'    => ['required', Rule::in(['arrival','departure'])],
            'body'    => ['required','string','max:500'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $attendance_template->update($data);

        if ($attendance_template->is_active) {
            AttendanceMessageTemplate::where('school_id',session('school_id'))
                ->where('id','!=',$attendance_template->id)
                ->where('type', $attendance_template->type)
                ->update(['is_active'=>false]);
        }

        // get all templates for this school
        // and push to redis
        $templates = AttendanceMessageTemplate::where(['school_id' => session('school_id'), 'is_active' => true])->get();
        app(RedisService::class)->upsertMessageTemplates($templates, session('channel_id'));

        return back()->with('ok','Template updated.');
    }

    public function destroy(AttendanceMessageTemplate $attendance_template){
        $attendance_template->delete();

        // get all templates for this school
        // and push to redis
        $templates = AttendanceMessageTemplate::where(['school_id' => session('school_id'), 'is_active' => true])->get();
        app(RedisService::class)->upsertMessageTemplates($templates, session('channel_id'));
        
        return back()->with('ok','Deleted.');
    }

    // quick preview without saving
    public function preview(Request $req, AttendanceMessageRenderer $renderer){
        $data = $req->validate([
            'body' => ['required','string','max:500'],
            'sample' => ['array']
        ]);

        $tpl = new AttendanceMessageTemplate(['body'=>$data['body']]);
        $message = $renderer->render($tpl, $data['sample'] ?? []);
        return response()->json(['message'=>$message]);
    }
}
