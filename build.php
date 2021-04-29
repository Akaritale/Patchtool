<?php

$pharFile = 'patchtool.phar';

$phar = new Phar($pharFile);
$phar->startBuffering();
$phar->buildFromDirectory(__DIR__ . '/app');

$defaultStub = Phar::createDefaultStub('cli.php');
$stub = "#!/usr/bin/env php \n" . $defaultStub;
$phar->setStub($stub);

$phar->stopBuffering();

try {
    $phar->compressFiles(Phar::GZ);
} catch (Exception $e) {
    echo $e->getMessage();
}

echo "{$pharFile} successfully created." . PHP_EOL;
