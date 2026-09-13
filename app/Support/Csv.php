<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A CSV download, written straight to the response so a long export never has
 * to sit in memory.
 *
 * Two things every export here needs:
 *   - a UTF-8 byte-order mark, or Excel shows names with accents as garbage;
 *   - cells that start with = + - @ are prefixed with ', so a member name or a
 *     comment can never run as a spreadsheet formula (CSV injection).
 */
class Csv
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headings);

            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'cell'], $row));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Numbers go out as numbers (a negative one is still a number, not a
     * formula); null is an empty cell; text that could be read as a formula is
     * neutralised.
     */
    public static function cell(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
