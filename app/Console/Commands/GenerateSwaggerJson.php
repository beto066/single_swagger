<?php

namespace App\Console\Commands;

use App\Services\GenerateSwaggerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSwaggerJson extends Command
{
    private GenerateSwaggerService $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:swagger {routeFile} {--prefix=} {--tenants=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->service = app(GenerateSwaggerService::class);

        $routeName = str_replace('.php', '', $this->argument('routeFile'));

        $swagger = $this->service->generateJson($this->argument('routeFile'), $this->option('prefix'), $this->option('tenants'));

        $json = json_encode($swagger, JSON_PRETTY_PRINT);
        $swaggerFileName = $routeName . '.json';
        $directory = public_path("api-documentation/routes/");

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($directory . $swaggerFileName, $json);

        $filesJsonPath = $directory . 'files.json';

        if (File::exists($filesJsonPath)) {
            // Read existing JSON content
            $filesJsonContent = File::get($filesJsonPath);
            $existingRoutes = json_decode($filesJsonContent, true);

            // If the route is not already listed, add it
            if (!in_array($swaggerFileName, $existingRoutes)) {
                $existingRoutes[] = $swaggerFileName;
                File::put($filesJsonPath, json_encode($existingRoutes, JSON_PRETTY_PRINT));
            }
        } else {
            // If files.json doesn't exist, create it and add the route
            $newRoutes = [$swaggerFileName];
            File::put($filesJsonPath, json_encode($newRoutes, JSON_PRETTY_PRINT));
        }

        $this->info('Swagger JSON generated successfully.');
    }
}
