<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function download()
    {
        // Only Admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $dbName = env('DB_DATABASE');
        $filename = "backup_" . $dbName . "_" . date('Y-m-d_H-i-s') . ".sql";
        
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $tables = DB::select('SHOW TABLES');
            $dbName = env('DB_DATABASE');
            $colName = "Tables_in_" . $dbName;

            echo "-- E-Rapor Database Backup\n";
            echo "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$colName;
                
                // Structure
                $createTable = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};
                echo "DROP TABLE IF EXISTS `$tableName`;\n";
                echo $createTable . ";\n\n";

                // Data
                // Use chunking to avoid memory issues
                DB::table($tableName)->orderBy(DB::raw('1'))->chunk(200, function($rows) use ($tableName) {
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if (is_null($value)) {
                                $values[] = "NULL";
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        echo "INSERT INTO `$tableName` VALUES (" . implode(', ', $values) . ");\n";
                    }
                });
                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        };

        return response()->stream($callback, 200, $headers);
    }
}
