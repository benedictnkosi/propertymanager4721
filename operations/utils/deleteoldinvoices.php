<?php

require_once (__DIR__ . '/../app/application.php');

$folderPath = __DIR__ . '/../../../invoices/';
if (file_exists($folderPath)) {
    foreach (new DirectoryIterator($folderPath) as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        if ($fileInfo->isFile() && time() - $fileInfo->getCTime() >= INVOICE_RETENTION_DAYS*24*60*60) {
            unlink($fileInfo->getRealPath());
        }
    }
}else{
    echo "folder not found";
}
?>