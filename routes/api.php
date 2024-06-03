<?php

use App\Http\Controllers\GetRoutesController;
use App\Http\Controllers\SwaggerJsonController;
use App\Http\Requests\GetRoutesRequest;
use Illuminate\Support\Facades\Route;

Route::get('/swagger-routes', GetRoutesController::class)->name('api.swagger.routes');

Route::middleware('swagger-prefix:api')->get('/swagger-json/{fileName}', SwaggerJsonController::class)->name('api.swagger.api-documentation');


