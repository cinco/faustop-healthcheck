<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    public function healthCheck()
    {
        try {
            $redis = Redis::connection();
            $DB = DB::connection();

            $redisPing = $redis->ping() ? 'Running at ' . Carbon::now() : 'down';
            $DBPing = $DB->getPdo() ? 'Running at ' . Carbon::now() : 'down';

            return [
                'redis' => $redisPing,
                'DB' => $DBPing
            ];
        } catch (\Throwable $th) {
            Log::error($_SERVER['HOSTNAME'] . ' - [' . $_SERVER["APP_NAME"] . '] - Healthcheck: database is down');
            Log::error($th->getMessage());

            $message_error = '<img src="https://j.gifs.com/m8LGL3.gif"></img>';
            return response('Database is <b>down</b> <br><img src="https://j.gifs.com/m8LGL3.gif"></img><br> Database is <b>down</b><br> msg from: <b>' . $_SERVER['HOSTNAME'] . '</b>', 500);
        }
    }
}