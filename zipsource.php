<?php 
set_time_limit(1000);

define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);

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

///////////
// BEGIN //
///////////

$res = [
    'done' => 0,
    'name' => '',
    'time' => 0
];
$log = BASE_PATH . 'logs.txt';
// Create log
if (!pathExists($log)) {
    file_put_contents($log, json_encode($res));
}
if (isset($_POST['zip_source']) && $_POST['zip_source'] == '1') {
    $src = BASE_PATH;
    $dstName = 'zipsouce.zip';
    $dst = BASE_PATH . $dstName;
    if (file_exists($dst)) {
        unlink($dst);
    }
    $res['name'] = $dstName;
    $res['time'] = date('Y/m/d H:i:s');
    file_put_contents($log, json_encode($res));
    zip($src, $dst, ['upload']);
    $res['done'] = 1;
    file_put_contents($log, json_encode($res));
    header('location: ');
}

$dataLog = (array)json_decode(file_get_contents($log));
$downPath = BASE_PATH . 'zipsouce.zip';
$downName = 'zipsouce.zip';

if (!pathExists($downPath)) {
    $downName = '#';
}
?>

<form action="" method="POST" accept-charset="utf-8">
    <input type="hidden" name="zip_source" value="1">
    <button type="submit">Zip</button>
</form>

<div>
    File zip: <?php echo ($downName == '#' ? '...' : "<a href='{$downName}'>{$downName}</a>") ?>
</div>
