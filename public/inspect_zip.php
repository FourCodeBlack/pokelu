<?php
$zipPath = __DIR__ . '/../storage/app/private/pokelu-layout.zip';
if (!file_exists($zipPath)) {
    die("File not found at: " . $zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath) === TRUE) {
    echo "<h1>ZIP Contents</h1><ul>";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $fileinfo = $zip->statIndex($i);
        echo "<li>" . htmlspecialchars($filename) . " (" . $fileinfo['size'] . " bytes)</li>";
    }
    echo "</ul>";
    $zip->close();
} else {
    echo "Failed to open zip file.";
}
