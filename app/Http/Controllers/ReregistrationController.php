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
     */
    public function index(): \Illuminate\Http\Response
    {
        $config = Config::where('key', 're_registration')->first()->value;
        if ($config == 'true') {
            return view('student.registration.index');
        } else {
            return redirect()->back()->with('error', 'Wahhh, pendaftaran ulang belum dibuka nih');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\Response
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
                return redirect()->route('student.re-registration.index')->with('error', 'Kamu sudah melakukan perpanjangan');
            }
        } catch (\Throwable $th) {
            return redirect()->route('student.re-registration.index')->with('error', 'Gagal melakukan perpanjangan');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): \Illuminate\Http\Response
    {
        //
    }
}
