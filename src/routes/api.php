<?php

use Cinco\FaustopHealthcheck\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/api/health-check/readiness', [HealthCheckController::class, 'healthCheck'])->name('api.health-check.readiness');
Route::get('/api/health-check/liveness', [HealthCheckController::class, 'healthCheck'])->name('api.health-check.liveness');
