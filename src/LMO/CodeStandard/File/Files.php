<?php

namespace LMO\CodeStandard\File;

class Files implements \Iterator
{

    private $index = 0;
    /**
     * @var File[]
     */
    private $files = [];

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->files[$this->index];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->files[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @param File $file
     * return void
     */
    public function push($file)
    {
        $this->files[] = $file;
    }

    /**
     * @return array
     */
    public function getFileNames()
    {
        return array_map(function ($file) {
            return $file->getName();
        }, $this->files);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->files);
    }

    /**
     * @param array $extensions
     * @return self
     */
    public function filterByExtensions($extensions)
    {
        $filteredFiles = new Files();
        foreach ($this->files as $file) {
            if (isset($extensions[$file->getExtension()])) {
                $filteredFiles->push($file);
            }
        }
        return $filteredFiles;
    }

    /**
     * @param array $standards
     * @return self[]
     */
    public function groupByStandard($standards)
    {
        $secondaryStandards = $standards;
        unset($secondaryStandards['main']);

        $files = [];
        foreach ($this->files as $file) {
            $standardFound = false;
            foreach ($secondaryStandards as $standardName => $standard) {
                if (strpos($file->getName(), $standard['folder']) === 0) {
                    $files[$standardName] = $files[$standardName] ?? new Files();
                    $files[$standardName]->push($file);
                    $standardFound = true;
                    break;
                }
            }
            if (!$standardFound) {
                $files['main'] = $files['main'] ?? new Files();
                $files['main']->push($file);
            }
        }
        return $files;
    }

    /**
     * @param string $fileName
     * @return bool|File
     */
    public function getFile($fileName)
    {
        foreach ($this->files as $file) {
            if ($file->getName() === $fileName) {
                return $file;
            }
        }
        return false;
    }
}
