<?php

namespace LMO\Hook\Checker;

use LMO\Hook\File\Files;
use Symfony\Component\Process\Process;

class PhpLintChecker extends CheckerAbstract
{
    protected $extensions = ['php' => true];

    /**
     * @param Files $files
     * @return array An array of error messages
     */
    protected function getErrors($files)
    {
        $errors = [];
        foreach ($files as $file) {
            $process = new Process(
                'php -l ' . $file->getName()
            );
            $process->run();
            $result = $process->getOutput();
            $errors = array_merge(
                $errors,
                array_filter(explode("\n", $result), function ($line) {
                    return strpos($line, 'Parse error') === 0;
                })
            );
        }

        return $errors;
    }
}
