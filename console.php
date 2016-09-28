#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(
    new \LMO\CodeStandard\Command\CheckStagedCommand(__DIR__)
);
$application->run();
