<?php

/**
 * Flot unit tests bootstrap file
 * 
 * This assumes there is an autoload.php file in the vendor directory above this package
 * 
 * @author Jason Roman <j@jayroman.com>
 */
$file = __DIR__.'/../vendor/autoload.php';

if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $file;