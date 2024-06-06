<?php

use Illuminate\Support\Facades\Route;
use SingleSoftware\SinglesSwagger\Http\Controllers\SweggerViewsController;

Route::middleware('env.check')->get('/api-doc', [SweggerViewsController::class, 'swaggerRouetesView'])->name('view.swagger.routes');

Route::middleware('env.check')->get('/api-doc/{routeName}', [SweggerViewsController::class, 'swaggerApiView'])->name('view.swagger.api-documentation');
