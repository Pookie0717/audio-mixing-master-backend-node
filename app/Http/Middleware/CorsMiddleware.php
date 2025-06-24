<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Asm89\Stack\CorsService;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


     protected $cors;

    public function __construct()
    {
        $this->cors = new CorsService([
            'allowedHeaders' => ['*'],
            'allowedMethods' => ['*'],
            'allowedOrigins' => ['*'],
            'exposedHeaders' => false,
            'maxAge' => false,
            'supportsCredentials' => false,
        ]);
    }
    public function handle($request, Closure $next)
    {
        if ($this->cors->isCorsRequest($request)) {
            $response = $this->cors->handlePreflightRequest($request);
            
            if ($response) {
                return $response;
            }
        }

        $response = $next($request);

        return $this->cors->addActualRequestHeaders($response, $request);
    }
}
