<?php

use Illuminate\Support\Facades\Route;
use SingleSoftware\SinglesSwagger\Http\Controllers\GetRoutesController;
use SingleSoftware\SinglesSwagger\Http\Controllers\SwaggerJsonController;

Route::get('/swagger-routes', GetRoutesController::class)->name('api.swagger.routes');

Route::middleware('swagger-prefix:api')->get('/swagger-json/{fileName}', SwaggerJsonController::class)->name('api.swagger.api-documentation');


