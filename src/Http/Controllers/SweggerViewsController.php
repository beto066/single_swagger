<?php

namespace SingleSoftware\SinglesSwagger\Http\Controllers;

use Illuminate\Http\Request;

class SweggerViewsController extends Controller
{
    public function swaggerApiView(string $routeName) {
        return view('swagger::swagger', ['routeName' => $routeName]);
    }

    public function swaggerRouetesView() {
        return view('swagger::navigation');
    }
}
