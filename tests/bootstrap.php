<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\VarDumper\VarDumper;

VarDumper::setHandler(function ($var) {
    // Suppress VarDumper output in tests
});
