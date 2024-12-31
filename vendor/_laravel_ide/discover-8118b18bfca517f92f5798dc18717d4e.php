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

echo collect(\Illuminate\Support\Facades\Gate::abilities())
    ->map(function ($policy, $key) {
        $reflection = new \ReflectionFunction($policy);

        $policyClass = null;

        if (get_class($reflection->getClosureThis()) === \Illuminate\Auth\Access\Gate::class) {
            $vars = $reflection->getClosureUsedVariables();

            if (isset($vars['callback'])) {
                [$policyClass, $method] = explode('@', $vars['callback']);

                $reflection = new \ReflectionMethod($policyClass, $method);
            }
        }
        return [
            'key' => $key,
            'uri' => $reflection->getFileName(),
            'policy_class' => $policyClass,
            'lineNumber' => $reflection->getStartLine(),
        ];
    })
    ->merge(
        collect(\Illuminate\Support\Facades\Gate::policies())->flatMap(function ($policy, $model) {
            $methods = (new ReflectionClass($policy))->getMethods();

            return collect($methods)->map(function (ReflectionMethod $method) use ($policy) {
                return [
                    'key' => $method->getName(),
                    'uri' => $method->getFileName(),
                    'policy_class' => $policy,
                    'lineNumber' => $method->getStartLine(),
                ];
            })->filter(function ($ability) {
                return !in_array($ability['key'], ['allow', 'deny']);
            });
        }),
    )
    ->values()
    ->groupBy('key')
    ->toJson();
;
echo '__VSCODE_LARAVEL_END_OUTPUT__';

exit(0);
