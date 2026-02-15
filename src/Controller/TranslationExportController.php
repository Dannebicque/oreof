<?php

namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

final class TranslationExportController extends AbstractController
{
    #[Route('/admin/export/translations.xlsx', name: 'admin_export_translations_xlsx', methods: ['GET'])]
    public function exportTranslationsToExcel(): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $translationsDir = Path::join($projectDir, 'translations');

        if (!is_dir($translationsDir)) {
            return new Response('Directory "translations/" not found.', 404);
        }

        // Récupération des fichiers .yaml/.yml
        $files = array_values(array_filter(
            glob($translationsDir . '/*.{yaml,yml}', GLOB_BRACE) ?: [],
            fn(string $f) => is_file($f)
        ));

        if ($files === []) {
            return new Response('No translation YAML files found in translations/.', 404);
        }

        $spreadsheet = new Spreadsheet();

        // Supprime la feuille par défaut (on va tout recréer)
        $spreadsheet->removeSheetByIndex(0);

        foreach ($files as $index => $filePath) {
            $filename = basename($filePath);          // ex: messages.fr.yaml
            $sheetName = $this->safeSheetName($filename);

            $raw = Yaml::parseFile($filePath) ?? [];
            if (!is_array($raw)) {
                $raw = [];
            }

            $flat = $this->flattenYaml($raw); // ["foo.bar" => "baz", ...]

            $sheet = $spreadsheet->createSheet($index);
            $sheet->setTitle($sheetName);

            // En-têtes
            $sheet->setCellValue('A1', 'key');
            $sheet->setCellValue('B1', 'value');

            // Style simple en-tête
            $sheet->getStyle('A1:B1')->getFont()->setBold(true);
            $sheet->freezePane('A2');

            // Remplissage
            $row = 2;
            foreach ($flat as $key => $value) {
                $sheet->setCellValueExplicit("A{$row}", (string)$key, DataType::TYPE_STRING);

                // Si la valeur n'est pas un scalaire, on la sérialise (rare en traduction, mais safe)
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif ($value === null) {
                    $value = '';
                }

                $sheet->setCellValueExplicit("B{$row}", (string)$value, DataType::TYPE_STRING);
                $row++;
            }

            // Largeurs
            $sheet->getColumnDimension('A')->setWidth(60);
            $sheet->getColumnDimension('B')->setWidth(80);
            $sheet->getStyle('A2:A' . max(2, $row - 1))->getAlignment()->setWrapText(true);
            $sheet->getStyle('B2:B' . max(2, $row - 1))->getAlignment()->setWrapText(true);
        }

        $writer = new Xlsx($spreadsheet);

        // Génération en mémoire
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $date = (new \DateTimeImmutable())->format('Y-m-d_His');
        $downloadName = "translations_export_{$date}.xlsx";

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
            'Cache-Control' => 'max-age=0, private, must-revalidate',
        ]);
    }

    /**
     * Excel limite le nom d'onglet à 31 caractères et interdit : \ / ? * [ ]
     */
    private function safeSheetName(string $filename): string
    {
        $name = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', '-', $filename) ?? $filename;
        $name = trim($name);

        // 31 caractères max
        if (mb_strlen($name) > 31) {
            $name = mb_substr($name, 0, 31);
        }

        // fallback
        if ($name === '') {
            $name = 'sheet';
        }

        return $name;
    }

    /**
     * Aplati un tableau YAML en dot notation.
     * Exemple: ['foo' => ['bar' => 'baz']] => ['foo.bar' => 'baz']
     */
    private function flattenYaml(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $key = (string)$key;
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result += $this->flattenYaml($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }

        // Tri pour avoir un onglet stable
        ksort($result);

        return $result;
    }
}
