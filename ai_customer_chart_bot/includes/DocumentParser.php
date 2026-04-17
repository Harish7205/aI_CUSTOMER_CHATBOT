<?php
/**
 * Document parser - extracts text from uploaded files
 */

class DocumentParser {

    public static function extractText(string $filePath, string $mimeType): string {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'txt':
            case 'md':
            case 'csv':
                return self::extractPlainText($filePath);
            case 'pdf':
                return self::extractPdf($filePath);
            case 'doc':
            case 'docx':
                return self::extractDoc($filePath, $ext);
            default:
                return self::extractPlainText($filePath);
        }
    }

    private static function extractPlainText(string $filePath): string {
        $content = file_get_contents($filePath);
        return $content ?: '';
    }

    private static function extractPdf(string $filePath): string {
        if (!class_exists('Smalot\PdfParser\Parser')) {
            // Fallback: try to use pdftotext if available, else return empty
            if (function_exists('shell_exec')) {
                $out = @shell_exec('pdftotext -layout ' . escapeshellarg($filePath) . ' - 2>/dev/null');
                return $out ?: '';
            }
            return '';
        }
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filePath);
        return $pdf->getText();
    }

    private static function extractDoc(string $filePath, string $ext): string {
        if ($ext === 'docx') {
            return self::extractDocx($filePath);
        }
        // .doc is binary - would need COM or phpword; fallback to empty
        return '';
    }

    private static function extractDocx(string $filePath): string {
        $zip = new ZipArchive();
        if (!$zip->open($filePath)) return '';
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) return '';
        $xml = preg_replace('/<w:p[^>]*>/', "\n", $xml);
        $xml = preg_replace('/<[^>]+>/', ' ', $xml);
        return trim(preg_replace('/\s+/', ' ', $xml));
    }
}
