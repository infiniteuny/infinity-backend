<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationYear;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LandingController extends Controller
{
    public function index()
    {

        $response = Http::get(config('app.api_url') . '/api' . '/commitees?populate=photo,cabinet,division&sort[1]=division.priority&pagination[limit]=4');
        // dd(json_decode($response->body())->data);

        $data = collect(json_decode($response->body())->data)->map(function ($item) {
            return (object)[
                'name' => $item->attributes->name,
                'study_program' => $item->attributes->study_program,
                'year' => '20' . substr($item->attributes->student_id, 0, 2),
                'cabinet' => $item->attributes->cabinet->data->attributes->year,
                'instagram' => $item->attributes->instagram,
                'division' => $item->attributes->division->data->attributes->name,
                'priority' => $item->attributes->division->data->attributes->priority,
                'photo' => config('app.api_url') . $item->attributes->photo->data->attributes->url,
            ];
        });

        return view('landing.index')->with([
            'data' => $data,
            'config' => $this->config(),
        ]);
    }

    public function event()
    {
        return view('landing.event')->with([
            'config' => $this->config(),
        ]);
    }

    public function team()
    {
        $response = Http::get(config('app.api_url') . '/api' . '/commitees?populate=photo,cabinet,division&sort[1]=division.priority');

        $data = collect(json_decode($response->body())->data)->map(function ($item) {
            return (object)[
                'name' => $item->attributes->name,
                'study_program' => $item->attributes->study_program,
                'year' => '20' . substr($item->attributes->student_id, 0, 2),
                'cabinet' => $item->attributes->cabinet->data->attributes->year,
                'instagram' => $item->attributes->instagram,
                'division' => $item->attributes->division->data->attributes->name,
                'priority' => $item->attributes->division->data->attributes->priority,
                'photo' => config('app.api_url') . $item->attributes->photo->data->attributes->url,
            ];
        });

        return view('landing.index')->with([
            'data' => $data,
            'config' => $this->config(),
        ]);
    }

    public static function config()
    {
        $response = Http::get(config('app.api_url') . '/api/configs');
        $config = [];
        foreach (json_decode($response)->data as $item) {
            $config[$item->attributes->name] = $item->attributes->value;
        }
        return $config;
    }

    public function member()
    {
        # code...
    }
}
