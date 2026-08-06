<?php

use Cinco\FaustopHealthcheck\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/health-check/readiness', [HealthCheckController::class, 'readiness'])->name('web.health-check.readiness');
Route::get('/health-check/liveness', [HealthCheckController::class, 'liveness'])->name('web.health-check.liveness');
