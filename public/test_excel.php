<?php
require __DIR__ . '/../vendor/autoload.php';

try {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    echo "<h1>SUCCESS: Class Found!</h1>";
    echo "Version: " . \PhpOffice\PhpSpreadsheet\Spreadsheet::VERSION;
} catch (Error $e) {
    echo "<h1>ERROR: " . $e->getMessage() . "</h1>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
