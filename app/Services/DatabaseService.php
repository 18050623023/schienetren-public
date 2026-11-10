<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatabaseService
{

    function getEnumValues(string $table, string $column): array
    {
        $type = DB::selectOne("
            SELECT COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ", [$table, $column])->COLUMN_TYPE;

        preg_match("/^enum\((.*)\)$/", $type, $matches);

        $values = str_getcsv($matches[1], ',', "'");

        return array_combine($values, $values);
    }


}

?>
