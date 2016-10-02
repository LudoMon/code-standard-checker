<?php

namespace LMO\CodeStandard\Checker;

use LMO\CodeStandard\FileSystem\EditedFile;
use LMO\CodeStandard\FileSystem\FileManager;

abstract class CheckerAbstract
{
    protected $vendorDirectories;
    protected $scriptPath;
    protected $configPath;
    protected $extensions = [];
    protected $config = [];

    /**
     * @var FileManager
     */
    protected $fileManager;

    private $name;

    /**
     * @param FileManager $fileManager
     * @throws \Exception
     */
    public function __construct($fileManager)
    {
        if (empty($this->extensions)) {
            throw new \Exception(
                'A checker must care about file extensions'
            );
        }
        $this->fileManager = $fileManager;
    }

    /**
     * @param EditedFile[]  $files
     * @return array An array of error messages
     */
    abstract protected function getErrors($files);

    /**
     * @param EditedFile[] $files
     * @return array
     * @throws \Exception
     */
    public function checkFiles($files)
    {
        $filesToCheck = $this->fileManager->filterFilesByExtensions(
            $files,
            $this->extensions
        );
        if (empty($filesToCheck)) {
            return [];
        }
        return $this->getErrors($filesToCheck);
    }

    /**
     * @param string       $fileName (Absolute path)
     * @param EditedFile[] $files
     * @return EditedFile|bool
     */
    protected function findEditedFile($fileName, $files)
    {
        $fileName = str_replace('\\', '/', $fileName);
        foreach ($files as $file) {
            $name = $file->getName();
            $isFileFound = strpos($fileName, $name) + strlen($name) === strlen($fileName);
            if ($isFileFound) {
                return $file;
            }
        }
        return false;
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
     * @param string $configKey
     * @param string $errorMessage
     */
    protected function checkConfigFile($configKey, $errorMessage = '')
    {
        if (!file_exists($this->config[$configKey]) &&
            file_exists($this->configPath . DIRECTORY_SEPARATOR . $this->config[$configKey])
        ) {
            $this->config[$configKey] = $this->configPath . DIRECTORY_SEPARATOR . $this->config[$configKey];
        }
        if ($errorMessage && !file_exists($this->config[$configKey])) {
            throw new \InvalidArgumentException(
                $errorMessage . ' (' . $this->config[$configKey] . ')'
            );
        }
    }

    /**
     * @param array $vendorDirectories
     * @return static
     */
    public function setVendorDirectories($vendorDirectories)
    {
        $this->vendorDirectories = $vendorDirectories;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $scriptPath
     * @return static
     */
    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $scriptPath;
        return $this;
    }

    /**
     * @param string $configPath
     * @return static
     */
    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
        return $this;
    }
}
