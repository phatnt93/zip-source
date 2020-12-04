<?php

/**
 * Zip souce for share hosting
 */

set_time_limit(1000);

function pathExists($path){
    return file_exists($path);
}

function zip($pathDir, $pathZipFile = null, $excludeDirs = []){
    if(pathExists($pathDir) == false){
        return false;
    }
    if($pathZipFile == null || $pathZipFile == ''){
        return false;
    }
    // Get real path for our folder
    $rootPath = realpath($pathDir);
    // Initialize archive object
    $zip = new \ZipArchive();
    $zip->open($pathZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    // Create recursive directory iterator
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file){
        // Skip directories (they would be added automatically)
        if (!$file->isDir())
        {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            $relativePathArr = explode(DIRECTORY_SEPARATOR, $relativePath);
            array_pop($relativePathArr);
            if (count($excludeDirs) > 0) {
                foreach ($excludeDirs as $ke => $ve) {
                    if (!in_array($ve, $relativePathArr)) {
                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }else{
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
            
        }
    }
    // Zip archive will be created only after closing object
    $zip->close();
    return true;
}

$src = realpath(__DIR__ . '/');
$dst = $src . DIRECTORY_SEPARATOR . 'source_' . date('YmdHis') . '.zip';

zip($src, $dst, ['upload']);

echo "OK-{$dst}";