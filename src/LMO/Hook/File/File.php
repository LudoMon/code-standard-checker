<?php

namespace LMO\Hook\File;

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
     * @param array $lineNumbers
     */
    public function registerEditedLines($lineNumbers)
    {
        $this->editedLines = array_merge($this->editedLines, $lineNumbers);
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
