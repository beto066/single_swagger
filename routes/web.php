<?php

use Illuminate\Support\Facades\Route;
use SingleSoftware\SinglesSwagger\Http\Controllers\SweggerViewsController;

Route::get('/api-doc', [SweggerViewsController::class, 'swaggerRouetesView'])->name('view.swagger.routes');

Route::get('/api-doc/{routeName}', [SweggerViewsController::class, 'swaggerApiView'])->name('view.swagger.api-documentation');
