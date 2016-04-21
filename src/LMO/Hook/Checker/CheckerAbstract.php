<?php

namespace LMO\Hook\Checker;

use LMO\Hook\File\Files;

abstract class CheckerAbstract
{
    protected $name;
    protected $projectPath = '';
    protected $extensions = [];
    protected $config = [];

    /**
     * @param Files  $files
     * @return array An array of error messages
     */
    abstract protected function getErrors($files);

    /**
     * @param Files $files
     * @return array
     * @throws \Exception
     */
    public function checkFiles($files)
    {
        if (empty($this->name) || empty($this->extensions)) {
            throw new \Exception(
                'A checker must have a name and care about file extensions'
            );
        }

        if (empty($this->projectPath)) {
            throw new \Exception('Repository must be set before checking files');
        }

        $filesToCheck = $files->filterByExtensions($this->extensions);
        if ($filesToCheck->count() === 0) {
            return [];
        }
        return $this->getErrors($filesToCheck);
    }

    /**
     * @param string $projectPath
     * @return static
     */
    public function setProjectPath($projectPath)
    {
        $this->projectPath = $projectPath;
        return $this;
    }

    /**
     * @param array $config
     * @return static
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
