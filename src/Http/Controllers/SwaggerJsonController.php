<?php

namespace SingleSoftware\SinglesSwagger\Http\Controllers;

use Illuminate\Http\JsonResponse;
use SingleSoftware\SinglesSwagger\Http\Requests\SwaggerJsonRequest;
use SingleSoftware\SinglesSwagger\Services\GenerateSwaggerService;
use SingleSoftware\SinglesSwagger\Settings\SingleSwaggerSetting;

class SwaggerJsonController extends Controller
{
    public function __invoke(SwaggerJsonRequest $request, string $fileName, GenerateSwaggerService $service, SingleSwaggerSetting $settings): JsonResponse
    {
        if ($settings->is_preconfigured) {
            return response()->json($service->getPreConfiguredFile($fileName));
        }

        $prefix = $request->get('prefix');
        $tenant = $request->get('tenant');

        return response()->json($service->generateJson($fileName, $prefix, $tenant));
    }
}
