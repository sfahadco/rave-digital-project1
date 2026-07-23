<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends Controller
{
    public function health()
    {
        $health = [];

        try {
            DB::connection()->getPdo();
            $health['db'] = 'up';
        } catch (Throwable $th) {
            $health['db'] = 'down';
        }

        try {
            Redis::connection()->ping();
            $health['redis'] = 'up';
        } catch (Throwable $th) {
            $health['redis'] = 'down';
        }

        try {
            $ok = Http::timeout(3)->get('http://ollama:11434')->successful();
            $health['ollama'] = $ok ? 'up' : 'down';
        } catch (Throwable $th) {
            $health['ollama'] = 'down';
        }

        return response()->json($health);
    }
}
