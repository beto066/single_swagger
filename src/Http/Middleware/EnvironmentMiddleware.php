<?php

namespace SingleSoftware\SinglesSwagger\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedEnvironments = ['staging', 'develop', 'local'];

        if (!in_array(config('app.env'), $allowedEnvironments)) {
            abort(403, 'Acesso negado. Este recurso só está disponível em ambientes de staging, develop ou local.');
        }

        return $next($request);
    }
}
