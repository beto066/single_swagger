<?php

namespace SingleSoftware\SinglesSwagger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use SingleSoftware\SinglesSwagger\Http\Requests\GetRoutesRequest;
use SingleSoftware\SinglesSwagger\Services\GenerateSwaggerService;
use SingleSoftware\SinglesSwagger\Settings\SingleSwaggerSetting;

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
