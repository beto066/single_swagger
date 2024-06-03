<?php

namespace App\Http\Controllers;

use App\Http\Requests\SwaggerJsonRequest;
use App\Services\GenerateSwaggerService;
use App\Settings\SingleSwaggerSetting;
use Illuminate\Http\JsonResponse;

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
