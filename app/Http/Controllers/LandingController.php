<?php

namespace App\Http\Controllers;

use App\Mail\ContactUs;
use App\Models\Achievement;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    public function index()
    {
        $commitees = Http::get(config('app.api_url').'/api/cabinets?populate=commitees.photo,commitees.division&filters[year][$eq]='.Carbon::now()->year);
        $events = Http::get(config('app.api_url').'/api/events?populate=media&pagination[page]=1&pagination[pageSize]=5&sort[0]=event_date:desc');
        $products = Http::get(config('app.api_url').'/api/galleries?populate=photo&sort[0]=type&sort[1]=updatedAt');
        $testimonials = Http::get(config('app.api_url').'/api/testimonials?populate=photo');

        $data = count(json_decode($commitees->body())->data) > 0 ? json_decode($commitees->body())->data : json_decode(Http::get(config('app.api_url').'/api/cabinets?populate=commitees.photo,commitees.division&filters[year][$eq]='.Carbon::now()->subYear()->year)->body())->data;

        $data = collect($data)->map(function ($item) {
            $commitees = collect($item->attributes->commitees->data)->map(function ($commitee) use ($item) {
                return (object) [
                    'name' => $commitee->attributes->name,
                    'study_program' => $commitee->attributes->study_program,
                    'year' => '20'.substr($commitee->attributes->student_id, 0, 2),
                    'cabinet' => $item->attributes->year,
                    'instagram' => $commitee->attributes->instagram,
                    'division' => $commitee->attributes->division->data->attributes->name,
                    'priority' => $commitee->attributes->division->data->attributes->priority,
                    'photo' => config('app.api_url').$commitee->attributes->photo->data->attributes->formats->small->url,
                ];
            });

            return $commitees;
        });
        $data = $data->flatten()->sortBy('priority')->values()->all();

        $events = collect(json_decode($events->body())->data)->map(
            function ($item) {
                return (object) [
                    'id' => $item->id,
                    'title' => $item->attributes->title,
                    'content' => $item->attributes->content,
                    'type' => $item->attributes->type,
                    'event_date' => $item->attributes->event_date,
                    'published_at' => $item->attributes->publishedAt,
                    'media' => config('app.api_url').$item->attributes->media->data[0]->attributes->formats->medium->url,
                ];
            }
        );

        $products = collect(json_decode($products->body())->data)->map(function ($item) {
            return (object) [
                'title' => $item->attributes->title,
                'description' => $item->attributes->description,
                'url' => $item->attributes->url,
                'type' => $item->attributes->type,
                'photo' => config('app.api_url').$item->attributes->photo->data->attributes->formats->medium->url,
                'caption' => $item->attributes->photo->data->attributes->caption,
            ];
        });

        $testimonials = collect(json_decode($testimonials->body())->data)->map(function ($item) {
            return (object) [
                'name' => $item->attributes->name,
                'position' => $item->attributes->position,
                'testimonial' => $item->attributes->testimonial,
                'photo' => config('app.api_url').$item->attributes->photo->data->attributes->formats->thumbnail->url,
            ];
        });

        $count['member'] = floor(Member::where('status', 1)->count() / 10) * 10;
        $count['achievement'] = floor(Achievement::count() / 10) * 10;

        return view('landing.welcome')->with([
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
        $events = Http::get(config('app.api_url').'/api/events?populate=media');

        $events = collect(json_decode($events->body())->data)->map(
            function ($item) {
                return (object) [
                    'id' => $item->id,
                    'title' => $item->attributes->title,
                    'content' => $item->attributes->content,
                    'type' => $item->attributes->type,
                    'event_date' => $item->attributes->event_date,
                    'published_at' => $item->attributes->publishedAt,
                    'media' => config('app.api_url').$item->attributes->media->data[0]->attributes->formats->medium->url,
                ];
            }
        );

        return view('landing.event')->with([
            'events' => $events,
            'config' => $this->config(),
        ]);
    }

    public function eventDetail($event_id)
    {
        $event = Http::get(config('app.api_url').'/api/events/'.$event_id.'?populate=media');

        $event = collect(json_decode($event->body())->data)['attributes'];
        $event->media = config('app.api_url').$event->media->data[0]->attributes->url;

        return view('landing.event-detail')->with([
            'event' => $event,
            'config' => $this->config(),
        ]);
    }

    public function member()
    {
        return view('landing.member')->with([
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
        if (! $member) {
            $member = collect([]);
            $member->student_id = $request->student_id;
        } else {
            $member->year = '20'.substr($member->student_id, 0, 2);
            $member->end_date = $member->end_date ? Carbon::parse($member->end_date)->format('d/m/Y') : 'Sekarang';
        }

        return view('landing.member')->with([
            'config' => $this->config(),
            'member' => $member,
        ]);
    }

    public function team(Request $request)
    {
        if ($request->has('year')) {
            $commitees = Http::get(config('app.api_url').'/api/commitees?populate=photo,cabinet,division&filters[cabinet][year][$eq]='.$request->year.'&sort[1]=division.priority&pagination[page]=1&pagination[pageSize]=40');
            $data['commitees'] = collect(json_decode($commitees->body())->data)->map(function ($item) {
                return (object) [
                    'name' => $item->attributes->name,
                    'study_program' => $item->attributes->study_program,
                    'year' => '20'.substr($item->attributes->student_id, 0, 2),
                    'cabinet' => $item->attributes->cabinet->data->attributes->year,
                    'instagram' => $item->attributes->instagram,
                    'division' => $item->attributes->division->data->attributes->name,
                    'priority' => $item->attributes->division->data->attributes->priority,
                    'photo' => config('app.api_url').$item->attributes->photo->data->attributes->formats->small->url,
                ];
            });
        } else {
            $commitees = Http::get(config('app.api_url').'/api/commitees?populate=photo,cabinet,division&filters[cabinet][year][$eq]='.Carbon::now()->year.'&sort[1]=division.priority&pagination[page]=1&pagination[pageSize]=40');

            $commitees = count(json_decode($commitees->body())->data) > 0 ? json_decode($commitees->body())->data : json_decode(Http::get(config('app.api_url').'/api/commitees?populate=photo,cabinet,division&filters[cabinet][year][$eq]='.Carbon::now()->subYear()->year.'&sort[1]=division.priority&pagination[page]=1&pagination[pageSize]=40')->body())->data;

            $data['commitees'] = collect($commitees)->map(function ($item) {
                return (object) [
                    'name' => $item->attributes->name,
                    'study_program' => $item->attributes->study_program,
                    'year' => '20'.substr($item->attributes->student_id, 0, 2),
                    'cabinet' => $item->attributes->cabinet->data->attributes->year,
                    'instagram' => $item->attributes->instagram,
                    'division' => $item->attributes->division->data->attributes->name,
                    'priority' => $item->attributes->division->data->attributes->priority,
                    'photo' => config('app.api_url').$item->attributes->photo->data->attributes->formats->small->url,
                ];
            });
        }

        $data['cabinets'] = Http::get(config('app.api_url').'/api/cabinets');
        $data['cabinets'] = collect(json_decode($data['cabinets']->body())->data)->map(function ($item) {
            return (object) [
                'name' => $item->attributes->name,
                'year' => $item->attributes->year,
            ];
        })->flatten()->sortByDesc('year');

        return view('landing.team')->with([
            'data' => $data,
            'config' => $this->config(),
        ]);
    }

    public function contactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[a-zA-Z ]+$/u|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|regex:/^[a-zA-Z ]+$/u|max:100',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        try {
            Mail::to('infiniteuny@gmail.com')->send(new ContactUs($data));

            return redirect()->back()->with('success', 'Pesan berhasil dikirim');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Pesan gagal dikirim');
        }
    }

    public static function config()
    {
        $response = Http::get(config('app.api_url').'/api/configs');
        $config = [];
        foreach (json_decode($response)->data as $item) {
            $config[$item->attributes->name] = $item->attributes->value;
        }

        return $config;
    }
}
