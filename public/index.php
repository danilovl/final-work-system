<?php declare(strict_types=1);

use App\Kernel;

if (!is_file(dirname(__DIR__) . '/vendor/autoload_runtime.php')) {
    throw new LogicException('Symfony Runtime is missing. Try running "composer require symfony/runtime".');
}

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';
require_once dirname(__DIR__) . '/tracing.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
