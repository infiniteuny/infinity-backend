<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationYear;
use App\Models\StudyProgram;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $data['latestYear'] = OrganizationYear::orderBy('year', 'desc')->first();
        $data['organization'] = Organization::orderBy('created_at', 'asc')->limit(4)->get();

        foreach ($data['organization'] as $org) {
            $studyYear = substr($org->student_id, 0, 2);
            $studyProgram = StudyProgram::where('code', substr($org->student_id, 2, 5))->first();
            $org->studyYear = '20' . $studyYear;
            $org->studyProgram = $studyProgram->name;
        }

        return view('landing.index')->with(['data' => $data]);
    }

    public function event()
    {
        return view('landing.event');
    }

    public function team()
    {
        $data['latestYear'] = OrganizationYear::orderBy('year', 'desc')->first();
        $data['organization'] = Organization::orderBy('created_at', 'asc')->get();

        foreach ($data['organization'] as $org) {
            $studyYear = substr($org->student_id, 0, 2);
            $studyProgram = StudyProgram::where('code', substr($org->student_id, 2, 5))->first();
            $org->studyYear = '20' . $studyYear;
            $org->studyProgram = $studyProgram->name;
        }

        return view('landing.team')->with(['data' => $data]);
    }
}
