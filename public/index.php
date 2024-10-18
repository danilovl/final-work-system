<?php declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    // ElasticApm ignores E_USER_DEPRECATED and E_WARNING
    error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_WARNING);

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
