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
    /**
     * Liveness: prova que o processo PHP responde. Nada mais.
     *
     * NÃO adicione dependência externa aqui (Redis, banco, storage, HTTP).
     * Esta é a rota para a qual o healthcheck do container deve apontar: se ela
     * consultar uma dependência que oscila, o Docker mata um container saudável.
     */
    public function liveness()
    {
        return response()->json([
            'status' => 'alive',
            'timestamp' => Carbon::now()->toIso8601String(),
        ], 200);
    }

    /**
     * Readiness: verifica se as dependências externas respondem.
     *
     * Para orquestrador/balanceador decidir se manda tráfego — nunca para
     * decidir se mata o container.
     */
    public function readiness()
    {
        $checks = [
            'redis' => '',
            'DB' => '',
            'storage' => '',
        ];
        $checking = null;

        try {
            if (env('REDIS_HEALTH', true)) {
                $checking = 'redis';
                $redis = Redis::connection();
                $checks['redis'] = $redis->ping() ? 'Running at ' . Carbon::now() : 'down';
            }

            if (env('DB_HEALTH', true)) {
                $checking = 'DB';
                $checks['DB'] = DB::connection()->getPdo() ? 'Running at ' . Carbon::now() : 'down';
            }

            if (env('STORAGE_HEALTH', false)) {
                $checking = 'storage';
                $checks['storage'] = $this->checkStorage();
            }

            return response()->json($checks, $this->anyDown($checks) ? 503 : 200);
        } catch (\Throwable $th) {
            $hostname = $this->hostname();

            Log::error($hostname . ' - [' . $this->appName() . '] - Healthcheck: service is down');
            Log::error($th->getMessage());

            if ($checking !== null) {
                $checks[$checking] = 'down';
            }

            return response()->json(array_merge($checks, [
                'host' => $hostname,
                'error' => class_basename($th),
            ]), 503);
        }
    }

    /**
     * @deprecated 0.10.0 Use readiness(). Mantido para apps que apontam rota
     *             própria para este método.
     */
    public function healthCheck()
    {
        return $this->readiness();
    }

    /**
     * Dependência habilitada que respondeu falsy sem lançar exceção.
     * Sem isto o corpo diz "down" e o status diz 200.
     */
    private function anyDown(array $checks): bool
    {
        return in_array('down', $checks, true);
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

    private function hostname(): string
    {
        return ($_SERVER['HOSTNAME'] ?? '') ?: (gethostname() ?: 'unknown');
    }

    private function appName(): string
    {
        $name = ($_SERVER['APP_NAME'] ?? '') ?: config('app.name');

        return is_scalar($name) && (string) $name !== '' ? (string) $name : 'unknown';
    }
}
