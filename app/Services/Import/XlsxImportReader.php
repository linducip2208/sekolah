<?php

namespace App\Services\Import;

use InvalidArgumentException;

/**
 * XlsxImportReader — baca .xlsx tanpa dependensi tambahan.
 * Memakai ZipArchive + XML bawaan PHP (sheet1 + sharedStrings).
 * Return: ['headers' => [...], 'rows' => [[...], ...]]
 */
class XlsxImportReader
{
    /** @return array{headers:array<int,string>, rows:array<int,array<int,mixed>>} */
    public function read(string $path, int $maxRows = 5000): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('File Excel tidak dapat dibaca.');
        }

        try {
            $shared = $this->readSharedStrings($zip);
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                throw new InvalidArgumentException('Sheet1 tidak ditemukan di file Excel.');
            }

            $xml = simplexml_load_string($sheetXml);
            if ($xml === false) {
                throw new InvalidArgumentException('File Excel rusak.');
            }
            $rows = $xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [];

            $out = [];
            foreach ($rows as $row) {
                $cells = [];
                $cNodes = $row->xpath('./*[local-name()="c"]') ?: [];
                foreach ($cNodes as $c) {
                    $type = (string) ($c['t'] ?? '');
                    $vNodes = $c->xpath('./*[local-name()="v"]');
                    $v = $vNodes ? (string) $vNodes[0] : '';
                    if ($type === 's') {
                        $idx = is_numeric($v) ? (int) $v : -1;
                        $cells[] = $shared[$idx] ?? '';
                    } elseif ($type === 'inlineStr' || $type === 'str') {
                        $tNodes = $c->xpath('.//*[local-name()="t"]');
                        $cells[] = $tNodes ? (string) $tNodes[0] : $v;
                    } else {
                        $cells[] = $v;
                    }
                }
                // Skip baris kosong total
                if (array_filter($cells, fn ($x) => trim((string) $x) !== '') !== []) {
                    $out[] = $cells;
                }
                if (count($out) > $maxRows + 1) {
                    throw new InvalidArgumentException('Maksimal 5.000 baris per import. Pecah file menjadi beberapa batch.');
                }
            }

            if ($out === []) {
                throw new InvalidArgumentException('File Excel kosong.');
            }

            $headers = array_map(fn ($h) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), array_shift($out));

            return ['headers' => $headers, 'rows' => $out];
        } finally {
            $zip->close();
        }
    }

    /** @return array<int,string> */
    private function readSharedStrings(\ZipArchive $zip): array
    {
        $raw = $zip->getFromName('xl/sharedStrings.xml');
        if ($raw === false) {
            return [];
        }
        $xml = simplexml_load_string($raw);
        if ($xml === false) {
            return [];
        }
        $items = $xml->xpath('//*[local-name()="si"]') ?: [];
        $out = [];
        foreach ($items as $si) {
            // Gabungkan semua <t> (rich text support)
            $texts = $si->xpath('.//*[local-name()="t"]') ?: [];
            $out[] = implode('', array_map(fn ($t) => (string) $t, $texts));
        }

        return $out;
    }
}
