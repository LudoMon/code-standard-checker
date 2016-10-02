<?php

namespace LMO\CodeStandard\FileSystem;

class FileManager
{

    /**
     * @param EditedFile[] $files
     * @return string[]
     */
    public function getFileNames($files)
    {
        return array_map(function ($file) {
            return $file->getName();
        }, $files);
    }

    /**
     * @param EditedFile[] $files
     * @param array        $extensions
     * @return EditedFile[]
     */
    public function filterFilesByExtensions($files, $extensions)
    {
        return array_filter($files, function ($file) use ($extensions) {
            return isset($extensions[$file->getExtension()]);
        });
    }

    /**
     * @param EditedFile[] $files
     * @param array        $standards
     * @return EditedFile[][]
     */
    public function groupFilesByStandard($files, $standards)
    {
        $secondaryStandards = $standards;
        unset($secondaryStandards['main']);

        $groupedByStandardFiles = [];
        foreach ($files as $file) {
            $standardFound = false;
            foreach ($secondaryStandards as $standardName => $standard) {
                if (strpos($file->getName(), $standard['folder']) === 0) {
                    $groupedByStandardFiles[$standardName] =
                        $groupedByStandardFiles[$standardName] ?? [];
                    $groupedByStandardFiles[$standardName][] = $file;
                    $standardFound = true;
                    break;
                }
            }
            if (!$standardFound) {
                $groupedByStandardFiles['main'] =
                    $groupedByStandardFiles['main'] ?? [];
                $groupedByStandardFiles['main'][] = $file;
            }
        }
        return $groupedByStandardFiles;
    }

    /**
     * @param EditedFile[] $files
     * @param string       $fileName
     * @return bool|EditedFile
     */
    public function findFileByName($files, $fileName)
    {
        foreach ($files as $file) {
            if ($file->getName() === $fileName) {
                return $file;
            }
        }
        return false;
    }
}
