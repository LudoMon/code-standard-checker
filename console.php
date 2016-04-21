#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$config = [
    'projectPath' => '../web-metrics'
];

$application = new Application();
$application->add(
    new \LMO\Hook\Command\PreCommit(
        $config,
        [
            new \LMO\Hook\Checker\PhpLintChecker()
        ]
    )
);
$application->run();
