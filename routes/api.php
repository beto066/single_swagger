<?php

use Illuminate\Support\Facades\Route;
use SingleSoftware\SinglesSwagger\Http\Controllers\GetRoutesController;
use SingleSoftware\SinglesSwagger\Http\Controllers\SwaggerJsonController;

Route::get('/api-doc/swagger-routes', GetRoutesController::class)->name('api.swagger.routes');

Route::middleware('swagger-prefix:api')->get('/api-doc/swagger-json/{fileName}', SwaggerJsonController::class)->name('api.swagger.api-documentation');


