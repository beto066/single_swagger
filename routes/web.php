<?php

use App\Http\Controllers\SweggerViewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SweggerViewsController::class, 'swaggerRouetesView'])->name('view.swagger.routes');

Route::get('/{routeName}', [SweggerViewsController::class, 'swaggerApiView'])->name('view.swagger.api-documentation');
