<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function index()
    {
        $data['configs'] = Config::all()->mapWithKeys(function ($item) {
            return [$item['key'] => [
                'value' => $item['value'],
                'type' => $item['type'],
            ]];
        });

        return view('admin.config.index')->with([
            'data' => $data,
        ]);
    }

    public function update(Request $request)
    {
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
