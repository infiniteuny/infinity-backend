<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    public function index()
    {
        $data['configs'] = Config::all()->mapWithKeys(function ($item) {
            return [$item['key'] => $item['value']];
        });

        return view('admin.config.index')->with([
            'data' => $data,
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_target' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->all() as $key => $value) {
                    Config::where('key', $key)->update(['value' => $value]);
                }
            });

            return redirect()->back()->with('success', 'Pengaturan berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Pengaturan gagal diubah');
        }

        return redirect()->route('admin.config.index')->with('success', 'Config updated successfully');
    }
}
