<?php declare(strict_types=1);

use App\Kernel;

if (!is_file(dirname(__DIR__) . '/vendor/autoload_runtime.php')) {
    throw new LogicException('Symfony Runtime is missing. Try running "composer require symfony/runtime".');
}

$isE2eTestEnabled = ($_SERVER['E2E_TEST_ENABLE'] ?? null) === '1';
$isE2eTestRequest = ($_SERVER['HTTP_X_E2E_TEST'] ?? $_COOKIE['e2e_test'] ?? null) === '1';
$isPrivateNetwork = filter_var(
    $_SERVER['REMOTE_ADDR'] ?? '',
    FILTER_VALIDATE_IP,
    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
) === false;

if ($isE2eTestEnabled && $isE2eTestRequest && $isPrivateNetwork) {
    $_SERVER['APP_RUNTIME_OPTIONS'] = ['dotenv_path' => '.env.test'];
    $_SERVER['APP_E2E_TEST'] = '1';
}

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel(
        $context['APP_ENV'],
        (bool) $context['APP_DEBUG'],
    );
};
