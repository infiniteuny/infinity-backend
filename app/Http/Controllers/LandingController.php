<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LandingController extends Controller
{
    public function index()
    {
        $response = Http::get(config('app.api_url') . '/api/commitees?populate=photo,cabinet,division&filters[cabinet][year][$eq]=2022&sort[1]=division.priority');

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

        return view('welcome')->with([
            'data' => $data,
            'config' => $this->config(),
        ]);
    }

    public function event()
    {
        return view('event')->with([
            'config' => $this->config(),
        ]);
    }

    public function eventDetail()
    {
        return view('event-detail')->with([
            'config' => $this->config(),
        ]);
    }

    public function member()
    {
        return view('member')->with([
            'config' => $this->config(),
        ]);
    }

    public function memberChecker(Request $request)
    {
        # code...
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
}
