<?php

namespace App\Http\Controllers;

use App\Models\FreepikDownload;
use App\Http\Requests\StoreFreepikDownloadRequest;
use App\Http\Requests\UpdateFreepikDownloadRequest;
use App\Jobs\ProcessFreepikDownload;
use App\Models\Freepik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class FreepikDownloadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $data['freepik']['used'] = Freepik::whereRelation('freepikDownloads.users', 'student_id', $user->student_id)
            ->whereRelation('freepikDownloads.users', 'status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $data['freepik']['quota'] = $user->freepikDownloads ? ($user->freepikDownloads->limit + $user->freepikDownloads->limit_addons) : 3;
        $data['freepik']['total'] = Freepik::whereRelation('freepikDownloads.users', 'student_id', $user->student_id)
            ->whereRelation('freepikDownloads.users', 'status', 'completed')
            ->count();
        $data['freepik']['is_can_download'] = $data['freepik']['used'] < $data['freepik']['quota'];
        if ($request->ajax()) {
            if ($user->freepikDownloads()->exists()) {
                $freepikList = $user->freepikDownloads()->first()->freepiks()->latest()->get()->map(function ($data) {
                    return [
                        'id' => Crypt::encryptString($data->id),
                        'file_name' => $data->file_name,
                        'file_size' => $data->file_size ? number_format($data->file_size / 1048576, 2) . ' MB' : '0 MB',
                        'status' => $data->status,
                    ];
                });
            } else {
                $freepikList = [];
            }
            return DataTables::of($freepikList)
                ->addIndexColumn()
                ->make(true);
        }

        if (Auth::user()->hasRole('admin')) {
            return view('admin.freepik.index')->with([
                'data' => $data
            ]);
        } else {
            return view('student.freepik.index')->with([
                'data' => $data
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFreepikDownloadRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFreepikDownloadRequest $request)
    {

        $validate = Validator::make($request->all(), [
            'freepik_url' => 'required|url',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');
            return redirect()->back()->with('error', implode(' ', $error))->withInput();
        }

        if (Freepik::where('url', $request->freepik_url)->where('status', 'completed')->exists()) {
            $this->download(Crypt::encryptString(Freepik::where('url', $request->freepik_url)->first()->id));
        }

        if (Freepik::where('url', $request->freepik_url)->where('status', 'waiting')->exists()) {
            return redirect()->back()->with('error', 'URL freepik sudah ada di database, silahkan tunggu proses download selesai');
        }

        try {
            $user = Auth::user();
            if (!$user->freepikDownloads()->exists()) {
                $user->freepikDownloads()->create([
                    'limit' => 3,
                ]);
            }

            // if (
            //     $user->freepikDownloads()->first()->freepiks()
            //     ->where(function ($query) {
            //         $query->where('status', 'completed')->orWhere('status', 'waiting');
            //     })
            //     ->whereDate('created_at', Carbon::today())->count() >= $user->freepikDownloads()->first()->limit
            // ) {
            //     return redirect()->back()->with('error', 'Kuota download freepik kamu sudah habis, coba lagi besok');
            // }
            $download = $user->freepikDownloads()->first()->freepiks()->create([
                'url' => $request->freepik_url,
                'file_name' => $request->freepik_url,
                'status' => 'waiting',
            ]);

            ProcessFreepikDownload::dispatch($download->id);

            return redirect()->route('student.freepik.index')->with('success', 'File dalam antrian untuk di download');
        } catch (\Throwable $th) {
            return redirect()->route('student.freepik.index')->with('error', 'Gagal mengunduh file');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FreepikDownload  $freepikDownload
     * @return \Illuminate\Http\Response
     */
    public function show(FreepikDownload $freepikDownload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FreepikDownload  $freepikDownload
     * @return \Illuminate\Http\Response
     */
    public function edit(FreepikDownload $freepikDownload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFreepikDownloadRequest  $request
     * @param  \App\Models\FreepikDownload  $freepikDownload
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFreepikDownloadRequest $request, FreepikDownload $freepikDownload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FreepikDownload  $freepikDownload
     * @return \Illuminate\Http\Response
     */
    public function destroy(FreepikDownload $freepikDownload)
    {
        //
    }

    public function download($freepik)
    {
        $freepik = Freepik::find(Crypt::decryptString($freepik));
        if (Storage::disk('local')->exists($freepik->file_path)) {
            return Storage::disk('local')->download($freepik->file_path, $freepik->file_name);
        } else {
            return redirect()->route('student.freepik.index')->with('error', 'Yahh filenya ga ada');
        }
    }
}
