<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReregistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = Config::where('key', 're_registration')->first()->value;
        if ($config == 'true') {
            return view('student.registration.index');
        } else {
            return redirect()->back()->with('error', 'Wahhh, pendaftaran ulang belum dibuka nih ');
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            if (Carbon::parse($user->members->end_date)->year - Carbon::now()->year < 1) {
                DB::transaction(function () {
                    $user = auth()->user();
                    $user->members->end_date = Carbon::parse($user->members->end_date)->addYear();
                    $user->members->status = true;
                    $user->members->save();
                });
                return redirect()->route('student.re-registration.index')->with('success', 'Berhasil melakukan perpanjangan');
            } else {
                return redirect()->route('student.re-registration.index')->with('error', 'Anda sudah melakukan perpanjangan');
            }
        } catch (\Throwable $th) {
            return redirect()->route('student.re-registration.index')->with('error', 'Gagal melakukan perpanjangan');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
