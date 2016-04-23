<?php

namespace LMO\Hook;

use LMO\Hook\File;

class DiffParser
{
    /**
     * @param string $diff
     * @return File\Files Edited files
     */
    public function parse($diff)
    {
        $editedFiles = new File\Files();
        $filesDiff = explode('+++ b/', $diff);
        array_shift($filesDiff);
        foreach ($filesDiff as $fileDiff) {
            $fileChanges = explode("\n@@", $fileDiff);
            $fileName = trim(array_shift($fileChanges));
            $file = new File\File($fileName);
            foreach ($fileChanges as $fileChange) {
                preg_match('/\+([0-9]+)(,[0-9]+)? @@/', $fileChange, $matches);
                $changeStartLine = intval($matches[1]);
                $editedLinesCount = substr_count($fileChange, "\n+");
                $file->registerEditedLines(range(
                    $changeStartLine,
                    $changeStartLine + $editedLinesCount - 1
                ));
            }
            $editedFiles->push($file);
        }
        return $editedFiles;
    }
}
