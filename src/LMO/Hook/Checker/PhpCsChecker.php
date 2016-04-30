<?php

namespace LMO\Hook\Checker;

use LMO\Hook\File\Files;
use Symfony\Component\Process\Process;

class PhpCsChecker extends CheckerAbstract
{
    protected $extensions = ['php' => true];

    /**
     * @param Files $files
     * @return array An array of error messages
     */
    protected function getErrors($files)
    {
        $phpCsResult = $this->runPhpCs($files);
        $errorMessages = [];
        foreach ($phpCsResult->file as $phpCsFile) {
            $editedFile = $this->findEditedFile(
                (string) $phpCsFile['name'],
                $files
            );
            $editedFileName = pathinfo($editedFile->getName(), PATHINFO_FILENAME);
            $editedLines = $editedFile->getEditedLines();
            foreach ($phpCsFile->warning as $warning) {
                if (isset($editedLines[(int) $warning['line']])) {
                    $errorMessages[] = (string) $warning . ' in ' .
                        $editedFileName . ' on line ' . $warning['line'];
                }
            }
            foreach ($phpCsFile->error as $error) {
                if (isset($editedLines[(int) $error['line']])) {
                    $errorMessages[] = (string) $error . ' in ' .
                        $editedFileName . ' on line ' . $error['line'];
                }
            }
        }
        return $errorMessages;
    }

    /**
     * @param Files $files
     * @return \SimpleXMLElement
     */
    protected function runPhpCs($files)
    {
        $command = $this->vendorBinPath . 'phpcs' .
            ' --report=xml  --standard=' . $this->config['standard'] . ' ';
        $process = new Process(
            $command . implode(' ', $files->getFileNames()),
            $this->projectPath
        );
        $process->run();
        return new \SimpleXMLElement($process->getOutput());
    }
}
