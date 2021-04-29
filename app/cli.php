<?php

require_once 'vendor/autoload.php';

use App\Command\Generate;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Generate());

try {
    $application->run();
} catch (Exception $e) {
}