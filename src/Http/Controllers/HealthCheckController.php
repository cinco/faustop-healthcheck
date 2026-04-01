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
            $storagePing = '';

            if (env('REDIS_HEALTH', true)) {
                $redis = Redis::connection();
                $redisPing = $redis->ping() ? 'Running at ' . Carbon::now() : 'down';
            }

            if (env('DB_HEALTH', true)) {
                $DB = DB::connection();
                $DBPing = DB::connection()->getPdo() ? 'Running at ' . Carbon::now() : 'down';
            }

            if (env('STORAGE_HEALTH', false)) {
                $storagePing = $this->checkStorage();
            }

            return [
                'redis' => $redisPing,
                'DB' => $DBPing,
                'storage' => $storagePing,
            ];
        } catch (\Throwable $th) {
            Log::error($_SERVER['HOSTNAME'] . ' - [' . $_SERVER["APP_NAME"] . '] - Healthcheck: service is down');
            Log::error($th->getMessage());

            $message_error = '<img src="https://j.gifs.com/m8LGL3.gif"></img>';
            return response('Service is <b>down</b> <br><img src="https://j.gifs.com/m8LGL3.gif"></img><br> Service is <b>down</b><br> msg from: <b>' . $_SERVER['HOSTNAME'] . '</b>', 500);
        }
    }

    private function checkStorage(): string
    {
        $subpath = env('STORAGE_HEALTH_PATH', 'framework/cache');
        $path = storage_path($subpath);

        if (!is_dir($path)) {
            throw new \RuntimeException("Storage directory not found: {$subpath}");
        }

        return 'Running at ' . Carbon::now();
    }
}