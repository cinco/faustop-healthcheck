<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    public function healthCheck()
    {
        try{

            $redis = Redis::connection();
            $DB = DB::connection();

            if($redis->ping()){
                $redisPing = 'Running at '.Carbon::now();
            }else{
                $redisPing = 'down';
            }

            if($DB->getPDO()){
                $DBPing = 'Running at '.Carbon::now();
            }else{
                $DBPing = 'down';
            }

            return [
                'redis' => $redisPing,
                'DB' => $DBPing
            ];

        } catch(\Throwable $th) {
            Log::error($_SERVER['HOSTNAME'] . ' - ['.$_SERVER["APP_NAME"].'] - Healthcheck: database is down');
            Log::error($th->getMessage());
        }
        $message_error = '<img src="https://j.gifs.com/m8LGL3.gif"></img>';
        // return view('welcome', ['message_error' => $message_error ]);
        return response('Database is <b>down</b> <br><img src="https://j.gifs.com/m8LGL3.gif"></img><br> Database is <b>down</b><br> msg from: <b>' . $_SERVER['HOSTNAME'] .'</b>', 500);
    }
}