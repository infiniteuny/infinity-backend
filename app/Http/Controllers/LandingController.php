<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Member;
use App\Models\ProgramStudy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    public function index()
    {
        $response = Http::get(config('app.api_url') . '/api/commitees?populate=photo,cabinet,division&filters[cabinet][year][$eq]=2022&sort[1]=division.priority');
        $events = Http::get(config('app.api_url') . '/api/events?populate=media');
        $products = Http::get(config('app.api_url') . '/api/galleries?populate=photo');
        $testimonials = Http::get(config('app.api_url') . '/api/testimonials?populate=photo');
        // dd(json_decode($testimonials->body()));

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

        $events = collect(json_decode($events->body())->data)->map(
            function ($item) {
                return (object)[
                    'id' => $item->id,
                    'title' => $item->attributes->title,
                    'content' => $item->attributes->content,
                    'type' => $item->attributes->type,
                    'event_date' => $item->attributes->event_date,
                    'published_at' => $item->attributes->publishedAt,
                    'media' => config('app.api_url') . $item->attributes->media->data[0]->attributes->formats->medium->url,
                ];
            }
        );

        $products = collect(json_decode($products->body())->data)->map(function ($item) {
            return (object)[
                'title' => $item->attributes->title,
                'description' => $item->attributes->description,
                'url' => $item->attributes->url,
                'type' => $item->attributes->type,
                'photo' => config('app.api_url') . $item->attributes->photo->data->attributes->url,
                'caption' => $item->attributes->photo->data->attributes->caption,
            ];
        });

        $testimonials = collect(json_decode($testimonials->body())->data)->map(function ($item) {
            return (object)[
                'name' => $item->attributes->name,
                'position' => $item->attributes->position,
                'testimonial' => $item->attributes->testimonial,
                'photo' => config('app.api_url') . $item->attributes->photo->data->attributes->url,
            ];
        });

        $count['member'] = floor(Member::where('status', 1)->count() / 10) * 10;
        $count['achievement'] = floor(Achievement::count() / 10) * 10;

        return view('welcome')->with([
            'data' => $data,
            'events' => $events,
            'count' => $count,
            'products' => $products,
            'testimonials' => $testimonials,
            'config' => $this->config(),
        ]);
    }

    public function event()
    {
        $events = Http::get(config('app.api_url') . '/api/events?populate=media');

        $events = collect(json_decode($events->body())->data)->map(
            function ($item) {
                return (object)[
                    'id' => $item->id,
                    'title' => $item->attributes->title,
                    'content' => $item->attributes->content,
                    'type' => $item->attributes->type,
                    'event_date' => $item->attributes->event_date,
                    'published_at' => $item->attributes->publishedAt,
                    'media' => config('app.api_url') . $item->attributes->media->data[0]->attributes->formats->medium->url,
                ];
            }
        );

        return view('event')->with([
            'events' => $events,
            'config' => $this->config(),
        ]);
    }

    public function eventDetail($event_id)
    {
        $event = Http::get(config('app.api_url') . '/api/events/' . $event_id . '?populate=media');

        $event = collect(json_decode($event->body())->data)['attributes'];
        $event->media = config('app.api_url') . $event->media->data[0]->attributes->url;
        return view('event-detail')->with([
            'event' => $event,
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
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $member = Member::where('student_id', $request->student_id)->first();
        if (!$member) {
            $member = collect([]);
            $member->student_id = $request->student_id;
        } else {
            $member->year = '20' . substr($member->student_id, 0, 2);
            $member->end_date = $member->end_date ? Carbon::parse($member->end_date)->format('d/m/Y') : 'Sekarang';
        }

        return view('member')->with([
            'config' => $this->config(),
            'member' => $member
        ]);
    }

    public function team()
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

        return view('team')->with([
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
}
