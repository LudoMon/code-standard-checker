#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$config = \Symfony\Component\Yaml\Yaml::parse(
    file_get_contents(__DIR__ . '/config/config.yml')
);

$application = new Application();
$application->add(
    new \LMO\Hook\Command\PreCommit(
        $config,
        [
            new \LMO\Hook\Checker\PhpLintChecker(),
            new \LMO\Hook\Checker\ForbiddenWordsChecker(),
        ]
    )
);
$application->run();
