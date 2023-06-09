<?php

namespace Cinco\FaustopHealthcheck\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Routing\Controller as BaseController;

class HealthCheckController extends BaseController
{
    public function healthCheck()
    {
        try {
            $redisPing = '';
            $DBPing = '';

            if (env('REDIS_HEALTH', true)) {
                $redis = Redis::connection();
                $redisPing = $redis->ping() ? 'Running at ' . Carbon::now() : 'down';
            }

            if (env('DB_HEALTH', true)) {
                $DB = DB::connection();
                $DBPing = DB::connection()->getPdo() ? 'Running at ' . Carbon::now() : 'down';
            }

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