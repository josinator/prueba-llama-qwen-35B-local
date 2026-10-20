<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$env = strtolower($_SERVER['APP_ENV'] ?? 'dev');
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ($env === 'dev'));

if ($debug) {
    class_alias(\Symfony\Component\ErrorHandler\Debug::class, 'Debug');
    \Debug::enable();
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();

try {
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    $response = new JsonResponse([
        'error' => 'Internal Server Error',
        'message' => $debug ? $e->getMessage() : 'Something went wrong'
    ], 500);
    $response->send();
}

