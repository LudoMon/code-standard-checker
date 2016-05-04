<?php

namespace LMO\Hook\Checker;

use LMO\Hook\File\Files;
use Symfony\Component\Process\Process;

class EsLintChecker extends CheckerAbstract
{
    protected $extensions = ['js' => true];

    /**
     * @param Files $files
     * @return array An array of error messages
     */
    protected function getErrors($files)
    {
        $errorMessages = [];
        $esLintResults = $this->runEsLint($files);
        foreach ($esLintResults as $esLintFile) {
            $editedFile = $this->findEditedFile(
                $esLintFile->filePath,
                $files
            );
            $editedFileName = pathinfo($editedFile->getName(), PATHINFO_BASENAME);
            $editedLines = $editedFile->getEditedLines();
            foreach ($esLintFile->messages as $violation) {
                if (isset($editedLines[$violation->line])) {
                    $errorMessages[] = $violation->message . ' in ' .
                        $editedFileName . ' on line ' . $violation->line;
                }
            }
        }
        return $errorMessages;
    }

    /**
     * @param Files $files
     * @return mixed
     */
    protected function runEsLint($files)
    {
        $command = $this->vendorBinPaths['node'] . 'eslint_d' .
            ' --no-eslintrc --format=json  --config ' . $this->config['standard'];
        if (!empty($this->config['ignorePath'])) {
            $command .= ' --ignore-path ' . $this->config['ignorePath'];
        }
        $process = new Process(
            $command . ' ' . implode(' ', $files->getFileNames()),
            $this->projectPath
        );
        $process->run();
        return json_decode($process->getOutput());
    }
}
