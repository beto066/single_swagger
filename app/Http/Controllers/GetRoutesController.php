<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRoutesRequest;
use App\Services\GenerateSwaggerService;
use App\Settings\SingleSwaggerSetting;
use Illuminate\Http\JsonResponse;

class GetRoutesController extends Controller
{
    public function __invoke(GetRoutesRequest $request, GenerateSwaggerService $service, SingleSwaggerSetting $settings): JsonResponse
    {
        if ($settings->is_preconfigured) {
            return response()->json($service->getPreConfiguredFile('files.json'));
        }

        $routeDirectory = $request->get('route_directory');
        return response()->json($service->getAllFiles($routeDirectory?? 'routes'));
    }
}
