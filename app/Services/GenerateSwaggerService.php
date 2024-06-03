<?php

namespace App\Services;

use App\Settings\SingleSwaggerSetting;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionFunction;
use ReflectionMethod;

class GenerateSwaggerService
{
    public function getPreConfiguredFile(string $routeFileName) {
        $filePath = public_path("api-documentation/routes/$routeFileName");

        if (!file_exists($filePath)) {
            throw new ModelNotFoundException("O arquivo JSON '$filePath' não existe.");
        }

        $jsonContent = file_get_contents($filePath);

        $dataArray = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Erro ao decodificar o arquivo JSON '$filePath'.");
        }

        return $dataArray;
    }

    public function getAllFiles($directory = 'routes') {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path($directory), RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getFilename();
            }
        }

        return $files;
    }

    public function generateJson(string $routeFileName, ?string $prefix = null, ?string $tenant = null): array
    {
        $domain = url('/');

        if ($tenant) {
            $domain = DB::table('domains')->where(['tenant_id' => $tenant])->first()->domain;

            if (class_exists('\Stancl\Tenancy\Tenancy')) {
                tenancy()->initialize($tenant);
            } else {
                throw new Exception("\Stancl\Tenancy\Tenancy not found", 1);
            }
        }

        $routeFile = '/routes/' . $routeFileName;

        if (!File::exists(base_path($routeFile))) {
            throw new Exception("The specified route file does not exist: $routeFile");
        }

        // Clear current routes to avoid duplication
        Route::setRoutes(new \Illuminate\Routing\RouteCollection());

        // Load the specified routes file
        require base_path($routeFile);

        $routes = Route::getRoutes();
        $routeNamePattern = "/^([^:]+)\.([^:]+)\./";
        $paths = [];
        $tags = [];

        foreach ($routes as $route) {
            $middlewares =
            $methods = $route->methods();
            $uri = $route->uri();
            $action = $route->getAction();
            $name = $route->getName();
            $middlewares = $route->gatherMiddleware();

            if (preg_match($routeNamePattern, $name, $matches)) {
                $tag = $matches[2];
            } else {
                $tag = 'default';
            }

            $existingNames = array_column($tags, 'name');

            if (!in_array($tag, $existingNames)) {
                $tags[] =[
                    'name' => $tag,
                    'description' => '',
                ];
            }

            // Skip routes not relevant for API documentation
            if (in_array('GET', $methods) || in_array('POST', $methods) || in_array('PUT', $methods) || in_array('DELETE', $methods)) {
                $pathItem = [];

                foreach ($methods as $method) {
                    if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
                        $pathItem[strtolower($method)] = $this->formatePathItem($method, $middlewares, $prefix, $name, $action, $uri, $tag);
                    }
                }

                $routeFile = "/$prefix/$uri";

                $paths[preg_replace('/\/+/', '/', $routeFile)] = $pathItem;
            }
        }

        return [
            'swagger' => '2.0',
            'info' => [
                'title' => 'API Documentation',
                'version' => '1.0.0',
            ],
            'tags' => $tags,
            'paths' => $paths,
            'securityDefinitions' => [
                'Bearer' => [
                    'type' => 'apiKey',
                    "name" => "Authorization",
                    'in' => 'header',
                    'description' => 'Enter your bearer token in the format **Bearer &lt;token&gt;**',
                ],
            ],
            'security' => [
                [
                    'Bearer' => [],
                ]
            ]
        ];
    }

    private function toCamelCase($string) {
        // Quebrar a string pelo delimitador '.'
        $words = explode('.', $string);

        // Capitalizar cada palavra, exceto a primeira
        $camelCased = array_map(function($word, $index) {
            if ($index == 0) {
                return $word;
            }
            return ucfirst($word);
        }, $words, array_keys($words));

        // Juntar as palavras em uma única string
        return implode('', $camelCased);
    }

    private function formatPathParams(string $uri): array
    {
        $pattern = '/{([^}]+)}/';
        $parameters = [];

        if (preg_match_all($pattern, $uri, $matches)) {
            foreach ($matches[1] as $param) {
                $paramName = str_replace('?', '', $param);
                $required = (strpos($param, '?') !== false) ? false : true;

                $parameters[] = [
                    'name' => $paramName,
                    'in' => 'path',
                    'description' => '',
                    'type' => 'string',
                    'required' => $required,
                ];
            }
        }

        return $parameters;
    }

    private function formatePathItem(
        string $method,
        array $middlewares,
        ?string $prefix,
        string $name,
        array $action,
        string $uri,
        string $tag
    ): array
    {
        $auth = [];

        if (!empty(array_intersect($middlewares, [
            'auth',
            'auth:sanctum',
            'auth:api',
            'auth.basic',
            'auth.session',
        ]))) {
            $auth = [
                'security' => [
                    [
                        'Bearer' => [],
                    ],
                ],
            ];
        }

        if (!$prefix) {
            foreach ($middlewares as $middleware) {
                if (preg_match('/^swagger-prefix:(.*)/', $middleware, $matches)) {
                    $prefix = $matches[1];
                    break;
                }
            }
        }

        return [
            'summary' => '',
            'description' => '',
            'operationId' => $this->toCamelCase($name),
            'consumes' => 'application/json',
            'produces' => 'application/json',
            'parameters' => [
                // $this->formatAplicationJson(),
                ...$this->getRules($action['uses'], $method == 'GET') ?? [],
                ...$this->formatPathParams("/$prefix/$uri"),
            ],
            'tags' => [$tag],
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                ],
            ],
            ...$auth,
        ];
    }

    private function getRules(string|callable $controllerMethod, bool $isQueryParam = true): array
    {
        $reflection = null;

        if (is_callable($controllerMethod)) {
            $reflection = new ReflectionFunction($controllerMethod);
        } else {
            $parts = explode('@', $controllerMethod);
            $controllerName = $parts[0];
            $methodName = $parts[1];

            if (method_exists($controllerName, $methodName)) {
                $reflection = new ReflectionMethod($controllerName, $methodName);
            } else {
                return [];
            }
        }

        $parameters = $reflection?->getParameters();

        foreach ($parameters as $parameter) {
            if ($parameter->getType() && $this->checkFormRequest($parameter->getType()->getName())) {
                $formRequestClassName = $parameter->getType()->getName();

                $formRequestInstance = new $formRequestClassName();

                if (method_exists($formRequestInstance, 'rules')) {
                    try {
                        return $this->formatParamsFromRequest($formRequestInstance->rules(), false, $isQueryParam);
                    } catch (\Throwable $th) {
                        Log::error("Error: " . $th->getMessage());
                    }
                }

                return [];
            }
        }

        return [];
    }

    private function formatParamsFromRequest(array $formRequest, bool $isItem = false, bool $isQueryParam = true): array
    {
        $formattedParameters = [];
        $atributesArray = [];
        $atributesObjects = [];

        if (!$isItem && !$isQueryParam) {
            $keys = array_map(function($key) {
                return 'body.' . $key;
            }, array_keys($formRequest));

            $formRequest = array_combine($keys, array_values($formRequest));
        }

        if (!$this->isAssoc($formRequest)) {
            return $this->formatAllValidations($formRequest, '', $isItem, $isQueryParam);
        }

        foreach ($formRequest as $atribute => $rule) {
            if (strpos($atribute, '.') > 0) {
                $this->formatPrefix($atribute, '.', $rule, $atributesObjects);
            } else {
                $formattedParameters[$atribute] = $this->formatAllValidations($rule, $atribute, $isItem, $isQueryParam);
            }
        }

        foreach ($atributesObjects as $atribute => $value) {
            if (!$this->hasPrefix($value, '*')) {
                $formattedParameters[$atribute] = $this->formatAllValidations($formRequest, '', $isItem, $isQueryParam);
                $formattedParameters[$atribute]['type'] = 'object';
                $formattedParameters[$atribute]['properties'] = $this->formatParamsFromRequest($value, true);

                unset($formattedParameters[$atribute]['default']);
            } else {
                $formattedParameters[$atribute]['type'] = 'array';
                if (!$this->hasPrefix($value, '*.')) {
                    foreach ($value as $form) {
                        $validated = $this->formatAllValidations($form, '', $isItem, $isQueryParam);

                        if (
                            ($formattedParameters[$atribute]['items']['type'] ?? 'number') != 'string' ||
                            ($formattedParameters[$atribute]['items']['default'] ?? 0) != 'string'
                        ) {
                            $formattedParameters[$atribute]['items'] = $validated;
                        }
                    }
                } else {
                    $keys = array_map(function($key) {
                        return str_replace('*.', '', $key);
                    }, array_keys($value));

                    $newFormRequest = array_combine($keys, array_values($value));

                    $formattedParameters[$atribute]['items']['type'] = 'object';
                    $formattedParameters[$atribute]['items']['properties'] = $this->formatParamsFromRequest($newFormRequest, true, $isQueryParam);
                }

                unset($formattedParameters[$atribute]['default']);
            }
        }

        return $isItem? $formattedParameters : array_values($formattedParameters);
    }

    private function formatAllValidations(array|string $rule, string $atribute = '', bool $isItem = false, bool $isQueryParam = true): array
    {
        $arrayRule = $rule;
        if (is_string($rule)) {
            $arrayRule = explode('|', $rule);
        }

        return $this->formatValidations($atribute, $arrayRule, $isItem, $isQueryParam);
    }

    private function formatPrefix(string $atribute, string $breakVal, array|string $rule, &$atributes): void
    {
        $parts = explode($breakVal, $atribute, 2);

        $prefix = $parts[0];
        $rest = $parts[1];

        if (!isset($atributes[$prefix])) {
            $atributes[$prefix] = [];
        }

        $arrayRule = $rule;
        if (is_string($rule)) {
            $arrayRule = explode('|', $rule);
        }

        $atributes[$prefix][$rest] = $arrayRule;
    }

    private function formatValidations(string $atribute, array $rule, bool $isItem = false, bool $isQueryParam = true): array
    {
        if (!$isItem) {
            $parameter = [
                'name' => $isQueryParam? $atribute: 'body',
                'in' => $isQueryParam? 'query': 'body',
                'description' => '',
                'required' => false,
                'type' => 'string',
                'default' => 'string',
            ];
        } else {
            $parameter = [
                'required' => false,
                'type' => 'string',
                'default' => 'string',
            ];
        }

        if (in_array('required', $rule) !== false && in_array('sometimes', $rule) !== true && in_array('nullable', $rule) !== true) {
            $parameter['required'] = true;
        }

        if (strpos($atribute, '*') === 0) {
            $parameter['type'] = 'array';
            unset($parameter['default']);
            $parameter['schema'] = [
                'type' => 'array',
                'items' => $this->formatParamsFromRequest([ltrim($atribute, '*') => $rule], true, $isQueryParam),
            ];

            return $parameter;
        }

        if (in_array('integer', $rule) !== false || in_array('numeric', $rule) !== false || in_array('float', $rule) !== false) {
            $parameter['type'] = 'integer';
            $parameter['default'] = 0;

            return $parameter;
        }

        if (in_array('date', $rule)) {
            $parameter['format'] = 'date-time';
            $parameter['default'] = '2011-10-05T14:48:00.000Z';

            return $parameter;
        }

        if (in_array('email', $rule)) {
            $parameter['format'] = 'email';
            $parameter['default'] = 'email@email.com';

            return $parameter;
        }

        if (in_array('url', $rule)) {
            $parameter['format'] = 'url';
            $parameter['default'] = 'http://exemple.com';

            return $parameter;
        }

        if ($inRules = array_filter($rule, function($rule) {
            return strpos($rule, 'in:') === 0;
        })) {
            $values = [];
            foreach ($inRules as $inRule) {
                $values = array_merge($values, explode(',', str_replace('in:', '', $inRule)));
            }

            $parameter['enum'] = $values;
            $parameter['default'] = $values[0];
        }

        return $parameter;
    }

    private function isAssoc(array $value): bool
    {
        return !(is_array($value) && collect($value)->every(fn ($_, $key) => is_int($key)));
    }

    private function hasPrefix(array $value, string $prefix): bool
    {
        return (is_array($value) && collect($value)->every(fn ($_, $key) => strpos($key, $prefix) === 0));
    }

    private function checkFormRequest(string $className): bool
    {
        if (class_exists('\Illuminate\Foundation\Http\FormRequest')) {
            return is_subclass_of($className, \Illuminate\Foundation\Http\FormRequest::class);
        }
        return strpos($className, 'FormRequest') !== false;
    }
}
