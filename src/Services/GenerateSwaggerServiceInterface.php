<?php

namespace SingleSoftware\SinglesSwagger\Services;

interface GenerateSwaggerServiceInterface
{
    public function getPreConfiguredFile(string $routeFileName): array;

    public function getAllFiles($directory = 'routes'): array;

    public function generateJson(string $routeFileName, ?string $prefix = null, ?string $tenant = null): array;
}
