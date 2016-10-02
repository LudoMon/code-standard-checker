<?php

namespace LMO\CodeStandard\Checker;

use LMO\CodeStandard\FileSystem\Files;
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
        $this->checkConfigFile('standard', 'EsLint standard file not found');
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
        $results = [];
        $command = $this->vendorDirectories['node'] . 'eslint_d' .
            ' --no-eslintrc --format=json  --config ' . $this->config['standard'] .
            ' --stdin --stdin-filename=';
        foreach ($files as $file) {
            $fileName = $file->getName();
            $process = new Process(
                'git show :' . $fileName . ' | ' . $command . $fileName
            );
            $process->run();
            $fileViolations = json_decode($process->getOutput());
            if (!empty($fileViolations[0])) {
                $results[] = $fileViolations[0];
            }
        }
        return $results;
    }
}
