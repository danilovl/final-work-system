<?php declare(strict_types=1);

use App\Kernel;

if (!is_file(dirname(__DIR__) . '/vendor/autoload_runtime.php')) {
    throw new LogicException('Symfony Runtime is missing. Try running "composer require symfony/runtime".');
}

$ip = $_SERVER['REMOTE_ADDR'];
$subnet = '172.19.0.0/16';
$ipRange = explode('/', $subnet);
$ipStart = ip2long($ipRange[0]);
$mask = ~(pow(2, (32 - $ipRange[1])) - 1);
$ipLong = ip2long($ip);

if (isset($_SERVER['HTTP_X_E2E_TEST']) && $_SERVER['HTTP_X_E2E_TEST'] === '1' && ($ipLong & $mask) === ($ipStart & $mask)) {
    $_SERVER['APP_RUNTIME_OPTIONS'] = ['dotenv_path' => '.env.test'];
}

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel(
        $context['APP_ENV'],
        (bool) $context['APP_DEBUG'],
        (bool) $context['OPEN_TELEMETRY_ENABLE']
    );
};
