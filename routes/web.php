<?php

use Illuminate\Support\Facades\Route;
use SingleSoftware\SinglesSwagger\Http\Controllers\SweggerViewsController;

Route::get('/', [SweggerViewsController::class, 'swaggerRouetesView'])->name('view.swagger.routes');

Route::get('/{routeName}', [SweggerViewsController::class, 'swaggerApiView'])->name('view.swagger.api-documentation');
