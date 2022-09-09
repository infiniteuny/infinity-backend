<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeaderboardController extends Controller
{
    public function index()
    {
        return view('leaderboard')->with([
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
