<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

function pdfEncodedText(string $text): string
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
    if ($encoded === false) {
        $encoded = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
    }

    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
}

function pdfWrappedText(string $text, int $maxCharacters): array
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', trim($text));
    $encoded = $encoded === false ? $text : $encoded;
    $encoded = preg_replace('/\s+/', ' ', $encoded) ?? $encoded;
    if ($encoded === '') {
        return ['-'];
    }

    $lines = [];
    $line = '';
    foreach (explode(' ', $encoded) as $word) {
        foreach (explode("\n", wordwrap($word, $maxCharacters, "\n", true)) as $part) {
            $candidate = $line === '' ? $part : $line . ' ' . $part;
            if (strlen($candidate) > $maxCharacters && $line !== '') {
                $lines[] = $line;
                $line = $part;
            } else {
                $line = $candidate;
            }
        }
    }
    if ($line !== '') {
        $lines[] = $line;
    }

    return array_map(
        static fn (string $line): string => iconv('Windows-1252', 'UTF-8//IGNORE', $line) ?: $line,
        $lines
    );
}

function pdfDrawText(array &$commands, string $text, float $x, float $top, string $font = 'F1', float $size = 7.5): void
{
    $y = 595 - $top - $size;
    $commands[] = '0 0 0 rg';
    $commands[] = sprintf(
        'BT /%s %.2f Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET',
        $font,
        $size,
        $x,
        $y,
        pdfEncodedText($text)
    );
}

function pdfDrawCell(array &$commands, float $x, float $top, float $width, float $height, ?string $fill = null): void
{
    $bottom = 595 - $top - $height;
    if ($fill !== null) {
        $commands[] = sprintf('%s rg %.2f %.2f %.2f %.2f re f', $fill, $x, $bottom, $width, $height);
    }
    $commands[] = sprintf('0.68 0.72 0.76 RG %.2f %.2f %.2f %.2f re S', $x, $bottom, $width, $height);
}

function pdfPageHeader(int $pageNumber, int $recordCount): array
{
    $commands = [];
    pdfDrawText($commands, 'Asistencia por dia', 30, 25, 'F2', 17);
    pdfDrawText($commands, $recordCount . ' registros | Generado el ' . date('d/m/Y H:i'), 30, 49, 'F1', 9);
    pdfDrawText($commands, 'Pagina ' . $pageNumber, 750, 49, 'F1', 9);

    $headers = ['Empleado', 'Departamento', 'Fecha', 'Hora de ingreso', 'Hora de salida'];
    $widths = [200, 180, 130, 130, 135];
    $x = 30;
    foreach ($headers as $index => $header) {
        pdfDrawCell($commands, $x, 70, $widths[$index], 22, '0.91 0.94 0.97');
        pdfDrawText($commands, $header, $x + 4, 77, 'F2', 7.5);
        $x += $widths[$index];
    }

    return $commands;
}

$registros = getAttendanceReport();

$columnWidths = [200, 180, 130, 130, 135];
$pages = [pdfPageHeader(1, count($registros))];
$pageNumber = 1;
$top = 92;
$rows = $registros ?: [[
    'persona_nombre' => 'No hay registros de ingreso o salida',
    'departamento' => '',
    'fecha' => '',
    'hora_ingreso' => '',
    'hora_salida' => '',
]];

foreach ($rows as $registro) {
    $values = [
        (string) $registro['persona_nombre'],
        (string) $registro['departamento'],
        $registro['fecha'] !== '' ? formatDate((string) $registro['fecha']) : '',
        (string) ($registro['hora_ingreso'] ?? '-'),
        (string) ($registro['hora_salida'] ?? '-'),
    ];
    $wrapped = [];
    foreach ($values as $index => $value) {
        $maxCharacters = max(8, (int) floor(($columnWidths[$index] - 8) / 4.2));
        $wrapped[] = pdfWrappedText($value, $maxCharacters);
    }
    $lineCount = max(array_map('count', $wrapped));
    $rowHeight = $lineCount * 9 + 8;

    if ($top + $rowHeight > 560) {
        $pageNumber++;
        $pages[] = pdfPageHeader($pageNumber, count($registros));
        $top = 92;
    }

    $x = 30;
    foreach ($wrapped as $index => $lines) {
        pdfDrawCell($pages[$pageNumber - 1], $x, $top, $columnWidths[$index], $rowHeight);
        foreach ($lines as $lineIndex => $line) {
            pdfDrawText($pages[$pageNumber - 1], $line, $x + 4, $top + 4 + ($lineIndex * 9), 'F1', 7.5);
        }
        $x += $columnWidths[$index];
    }
    $top += $rowHeight;
}

$objects = [
    1 => '<< /Type /Catalog /Pages 2 0 R >>',
    3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
    4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
];
$pageReferences = [];
foreach ($pages as $index => $commands) {
    $contentId = 5 + ($index * 2);
    $pageId = $contentId + 1;
    $stream = implode("\n", $commands) . "\n";
    $objects[$contentId] = "<< /Length " . strlen($stream) . ">>\nstream\n" . $stream . 'endstream';
    $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
    $pageReferences[] = $pageId . ' 0 R';
}
$objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageReferences) . '] /Count ' . count($pages) . ' >>';
ksort($objects);

$pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
$offsets = [0];
foreach ($objects as $objectId => $object) {
    $offsets[$objectId] = strlen($pdf);
    $pdf .= $objectId . " 0 obj\n" . $object . "\nendobj\n";
}
$xrefOffset = strlen($pdf);
$objectCount = max(array_keys($objects)) + 1;
$pdf .= "xref\n0 " . $objectCount . "\n0000000000 65535 f \n";
for ($objectId = 1; $objectId < $objectCount; $objectId++) {
    $pdf .= sprintf('%010d 00000 n ', $offsets[$objectId]) . "\n";
}
$pdf .= "trailer\n<< /Size " . $objectCount . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte-registros-' . date('Y-m-d') . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;