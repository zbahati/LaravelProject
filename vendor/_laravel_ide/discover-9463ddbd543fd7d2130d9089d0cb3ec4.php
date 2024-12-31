<?php


error_reporting(E_ERROR | E_PARSE);

define('LARAVEL_START', microtime(true));

require_once __DIR__ . '/../autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';

class VsCodeLaravel extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        if (method_exists($this->app['log'], 'setHandlers')) {
            $this->app['log']->setHandlers([new \Monolog\Handler\ProcessHandler()]);
        }
    }
}

$app->register(new VsCodeLaravel($app));
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo '__VSCODE_LARAVEL_START_OUTPUT__';

function vsCodeGetRouterReflection(\Illuminate\Routing\Route $route)
{
    if ($route->getActionName() === 'Closure') {
        return new \ReflectionFunction($route->getAction()['uses']);
    }

    if (!str_contains($route->getActionName(), '@')) {
        return new \ReflectionClass($route->getActionName());
    }

    try {
        return new \ReflectionMethod($route->getControllerClass(), $route->getActionMethod());
    } catch (\Throwable $e) {
        $namespace = app(\Illuminate\Routing\UrlGenerator::class)->getRootControllerNamespace()
            ?? (app()->getNamespace() . 'Http\Controllers');

        return new \ReflectionMethod(
            $namespace . '\\' . ltrim($route->getControllerClass(), '\\'),
            $route->getActionMethod(),
        );
    }
}

echo collect(app('router')->getRoutes()->getRoutes())
    ->map(function (\Illuminate\Routing\Route $route) {
        try {
            $reflection = vsCodeGetRouterReflection($route);
        } catch (\Throwable $e) {
            $reflection = null;
        }

        return [
            'method' => collect($route->methods())->filter(function ($method) {
                return $method !== 'HEAD';
            })->implode('|'),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            'parameters' => $route->parameterNames(),
            'filename' => $reflection ? $reflection->getFileName() : null,
            'line' => $reflection ? $reflection->getStartLine() : null,
        ];
    })
    ->toJson();
;
echo '__VSCODE_LARAVEL_END_OUTPUT__';

exit(0);
