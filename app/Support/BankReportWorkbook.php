<?php

namespace App\Support;

use RuntimeException;
use XMLWriter;
use ZipArchive;

class BankReportWorkbook
{
    private const SHEET_NAMESPACE = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const OFFICE_RELATIONSHIPS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const PACKAGE_RELATIONSHIPS = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /** @param array<string, iterable<array<int, string|int|float|null>>> $sheets */
    public static function make(array $sheets): string
    {
        $file = tempnam(storage_path('framework/cache'), 'bank-report-');
        if ($file === false) {
            throw new RuntimeException('Could not create workbook file.');
        }

        try {
            $zip = new ZipArchive;
            if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Could not open workbook file.');
            }

            try {
                $workbook = self::document('workbook', self::SHEET_NAMESPACE);
                $workbook->writeAttribute('xmlns:r', self::OFFICE_RELATIONSHIPS);
                $workbook->startElement('sheets');

                $relationships = self::document('Relationships', self::PACKAGE_RELATIONSHIPS);
                $types = self::document('Types', 'http://schemas.openxmlformats.org/package/2006/content-types');
                self::element($types, 'Default', ['Extension' => 'xml', 'ContentType' => 'application/xml']);
                self::element($types, 'Default', [
                    'Extension' => 'rels',
                    'ContentType' => 'application/vnd.openxmlformats-package.relationships+xml',
                ]);
                self::element($types, 'Override', [
                    'PartName' => '/xl/workbook.xml',
                    'ContentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml',
                ]);

                $id = 0;
                foreach ($sheets as $name => $rows) {
                    $id++;
                    $path = "worksheets/sheet{$id}.xml";
                    self::element($workbook, 'sheet', ['name' => $name, 'sheetId' => (string) $id, 'r:id' => "rId{$id}"]);
                    self::element($relationships, 'Relationship', [
                        'Id' => "rId{$id}",
                        'Type' => self::OFFICE_RELATIONSHIPS.'/worksheet',
                        'Target' => $path,
                    ]);
                    self::element($types, 'Override', [
                        'PartName' => "/xl/{$path}",
                        'ContentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml',
                    ]);
                    self::add($zip, "xl/{$path}", self::worksheet($rows));
                }

                $workbook->endElement();
                $root = self::document('Relationships', self::PACKAGE_RELATIONSHIPS);
                self::element($root, 'Relationship', [
                    'Id' => 'rId1',
                    'Type' => self::OFFICE_RELATIONSHIPS.'/officeDocument',
                    'Target' => 'xl/workbook.xml',
                ]);
                self::add($zip, '[Content_Types].xml', self::finish($types));
                self::add($zip, '_rels/.rels', self::finish($root));
                self::add($zip, 'xl/workbook.xml', self::finish($workbook));
                self::add($zip, 'xl/_rels/workbook.xml.rels', self::finish($relationships));
            } finally {
                if (! $zip->close()) {
                    throw new RuntimeException('Could not finish writing the workbook.');
                }
            }

            $content = file_get_contents($file);
            if ($content === false) {
                throw new RuntimeException('Could not read the workbook file.');
            }

            return $content;
        } finally {
            unlink($file);
        }
    }

    /** @param iterable<array<int, string|int|float|null>> $rows */
    private static function worksheet(iterable $rows): string
    {
        $sheet = self::document('worksheet', self::SHEET_NAMESPACE);
        $sheet->startElement('sheetData');
        foreach ($rows as $row) {
            $sheet->startElement('row');
            foreach ($row as $value) {
                $sheet->startElement('c');
                if (is_int($value) || is_float($value)) {
                    $sheet->writeElement('v', (string) $value);
                } else {
                    $sheet->writeAttribute('t', 'inlineStr');
                    $sheet->startElement('is');
                    $sheet->startElement('t');
                    $sheet->writeAttribute('xml:space', 'preserve');
                    $sheet->text(preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value ?? '') ?? '');
                    $sheet->endElement();
                    $sheet->endElement();
                }
                $sheet->endElement();
            }
            $sheet->endElement();
        }
        $sheet->endElement();

        return self::finish($sheet);
    }

    private static function document(string $element, string $namespace): XMLWriter
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement($element);
        $xml->writeAttribute('xmlns', $namespace);

        return $xml;
    }

    /** @param array<string, string> $attributes */
    private static function element(XMLWriter $xml, string $name, array $attributes): void
    {
        $xml->startElement($name);
        foreach ($attributes as $key => $value) {
            $xml->writeAttribute($key, $value);
        }
        $xml->endElement();
    }

    private static function finish(XMLWriter $xml): string
    {
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private static function add(ZipArchive $zip, string $filename, string $xml): void
    {
        if (! $zip->addFromString($filename, $xml)) {
            throw new RuntimeException("Could not write {$filename} to the workbook.");
        }
    }
}
