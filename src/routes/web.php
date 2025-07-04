<?php

use Cinco\FaustopHealthcheck\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/health-check/readiness', [HealthCheckController::class, 'healthCheck'])->name('web.health-check.readiness');
Route::get('/health-check/liveness', [HealthCheckController::class, 'healthCheck'])->name('web.health-check.liveness');
