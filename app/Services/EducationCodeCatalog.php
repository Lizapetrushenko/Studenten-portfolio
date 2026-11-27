<?php

namespace App\Services;

class EducationCodeCatalog
{
    public function names(string $path): array
    {
        if (! is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        $names = [];
        $dates = [];
        try {
            $header = fgetcsv($handle, 0, ';', '"', '');
            if (! $header) {
                return [];
            }
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $codeIndex = array_search('Erkende opleidingscode', $header, true);
            $nameIndex = array_search('Erkende opleiding naam', $header, true);
            $dateIndex = array_search('Startdatum erkende opleiding', $header, true);
            if ($codeIndex === false || $nameIndex === false || $dateIndex === false) {
                return [];
            }
            while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
                $code = trim($row[$codeIndex] ?? '');
                $name = trim($row[$nameIndex] ?? '');
                $date = (int) ($row[$dateIndex] ?? 0);
                if ($code !== '' && $name !== '' && $date <= (int) date('Ymd')
                    && (! isset($dates[$code]) || $date > $dates[$code])) {
                    $names[$code] = $name;
                    $dates[$code] = $date;
                }
            }
        } finally {
            fclose($handle);
        }

        return $names;
    }
}
