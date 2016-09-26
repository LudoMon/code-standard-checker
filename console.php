#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$config = \Symfony\Component\Yaml\Yaml::parse(
    file_get_contents(__DIR__ . '/config/config.yml')
);

$application = new Application();
$application->add(
    new \LMO\CodeStandard\Command\CheckStaged(__DIR__, $config['checkers'])
);
$application->run();
