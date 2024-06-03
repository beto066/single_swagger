<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SweggerViewsController extends Controller
{
    public function swaggerApiView(string $routeName) {
        return view('swagger', ['routeName' => $routeName]);
    }

    public function swaggerRouetesView() {
        return view('navigation');
    }
}
