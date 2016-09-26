<?php

namespace LMO\CodeStandard\File;

class File
{
    protected $path;
    protected $extension;
    protected $editedLines = [];
    
    public function __construct($path)
    {
        $this->path = $path;
        $this->extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * @param string $lineNumber
     * @param string $lineContent
     */
    public function registerEditedLine($lineNumber, $lineContent)
    {
        $this->editedLines[$lineNumber] = $lineContent;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->path;
    }
    
    /**
     * @return array
     */
    public function getEditedLines()
    {
        return $this->editedLines;
    }
}
