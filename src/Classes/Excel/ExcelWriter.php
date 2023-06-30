<?php


namespace App\Classes\Excel;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class ExcelWriter
{
    protected Spreadsheet $spreadsheet;
    protected ?Worksheet $sheet;
    protected string $dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->dir = $kernel->getProjectDir() . '/public/modeles/';
    }

    public function nouveauFichier($libelle = '')
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->removeSheetByIndex(0);
        if ($libelle !== '') {
            $this->createSheet($libelle);
        }
    }

    /**
     * @param $libelle
     */
    public function createSheet($libelle): void
    {
        $this->spreadsheet->createSheet()->setTitle($libelle);
        $this->sheet = $this->spreadsheet->getSheetByName($libelle);
        $this->sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
    }

    public function setSheet(?Worksheet $sheet): void
    {
        $this->sheet = $sheet;
    }


    public function writeCellXY(int $col, int $row, ?string $value = '', array $options = []): void
    {
        $this->sheet->setCellValueByColumnAndRow($col, $row, $value);
        //traiter les options
        //style n'est pas un tableau
        if (is_array($options) && $this->sheet->getCellByColumnAndRow(
            $col,
            $row
        )) {
            foreach ($options as $key => $valeur) {
                switch ($key) {
                    case 'style':
                        switch ($valeur) {
                            case 'HORIZONTAL_RIGHT':
                                $this->sheet->getCellByColumnAndRow(
                                    $col,
                                    $row
                                )->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                                break;
                            case 'HORIZONTAL_CENTER':
                                $this->sheet->getCellByColumnAndRow(
                                    $col,
                                    $row
                                )->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                break;
                            case 'numerique':
                                $this->sheet->getCellByColumnAndRow(
                                    $col,
                                    $row
                                )->getStyle()->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                                break;
                        }
                        break;
                    case 'valign':
                        switch ($valeur) {
                            case 'VERTICAL_TOP':
                                $this->sheet->getCellByColumnAndRow(
                                    $col,
                                    $row
                                )->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                                break;
                            case 'VERTICAL_CENTER':
                                $this->sheet->getCellByColumnAndRow(
                                    $col,
                                    $row
                                )->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                                break;
                            case 'VERTICAL_BOTTOM':
                                $this->sheet->getCellByColumnAndRow(
                                    $col,
                                    $row
                                )->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);
                                break;
                        }
                        break;
                    case 'number_format':
                        $this->sheet->getCellByColumnAndRow(
                            $col,
                            $row
                        )->getStyle()->getNumberFormat()->setFormatCode($valeur);
                        break;
                    case 'color':
                        if (0 === mb_strpos($valeur, '#')) {
                            $valeur = mb_substr($valeur, 1, mb_strlen($valeur));
                        }

                        $this->sheet->getCellByColumnAndRow(
                            $col,
                            $row
                        )->getStyle()->getFont()->getColor()->setARGB('FF' . $valeur);
                        break;
                    case 'bgcolor':
                        if (0 === mb_strpos($valeur, '#')) {
                            $valeur = mb_substr($valeur, 1, mb_strlen($valeur));
                        }
                        $this->sheet->getCellByColumnAndRow(
                            $col,
                            $row
                        )->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($valeur);
                        break;
                    case 'font-size':
                        $this->sheet->getCellByColumnAndRow($col, $row)->getStyle()->getFont()->setSize($valeur);
                        break;
                    case 'font-weight':
                        $this->sheet->getCellByColumnAndRow($col, $row)->getStyle()->getFont()->setBold(true);
                        break;
                    case 'font-italic':
                        $this->sheet->getCellByColumnAndRow($col, $row)->getStyle()->getFont()->setItalic(true);
                        break;
                    case 'wrap':
                        $this->sheet->getCellByColumnAndRow($col, $row)->getStyle()->getAlignment()->setWrapText(true);
                        break;
                }
            }
        }
    }

    public function writeCellName($adresse, $value, array $options = []): void
    {
        $this->sheet->setCellValue($adresse, $value);

        if (is_array($options) && array_key_exists('style', $options)) {
            //style n'est pas un tableau
            switch ($options['style']) {
                case 'HORIZONTAL_RIGHT':
                    $this->sheet->getStyle($adresse)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    break;
                case 'HORIZONTAL_CENTER':
                    $this->sheet->getStyle($adresse)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    break;
                case 'numerique':
                    $this->sheet->getStyle($adresse)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    break;
                case 'numerique3':
                    $this->sheet->getStyle($adresse)->getNumberFormat()->setFormatCode('#,##0.000');
                    break;
            }
        }
    }

    public function colorCellXY($col, $lig, $couleur): void
    {
        $cell = Coordinate::stringFromColumnIndex($col) . $lig;
        $this->colorCells($cell, $couleur);
    }

    public function colorCells($cells, $couleur): void
    {
        $this->sheet->getStyle($cells)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($couleur);
    }

    public function borderCellsRange($col1, $lig1, $col2, $lig2): void
    {
        if ($col1 < $col2) {
            $cell1 = Coordinate::stringFromColumnIndex($col1) . $lig1;
            $cell2 = Coordinate::stringFromColumnIndex($col2) . $lig2;
            $this->borderCells($cell1 . ':' . $cell2);
        }
    }

    public function borderCells($cells): void
    {
        $this->sheet->getStyle($cells)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    public function getRowDimension(int $ligne, int $taille): void
    {
        $this->sheet->getRowDimension($ligne)->setRowHeight($taille);
    }

    public function getColumnDimension(string $col, int $taille): void
    {
        $this->sheet->getColumnDimension($col)->setWidth($taille);
    }

    public function mergeCellsCaR($col1, $lig1, $col2, $lig2): void
    {
        if ($col1 <= $col2) {
            $cell1 = Coordinate::stringFromColumnIndex($col1) . $lig1;
            $cell2 = Coordinate::stringFromColumnIndex($col2) . $lig2;
            $this->mergeCells($cell1 . ':' . $cell2);
        }
    }

    public function mergeCells($cells): void
    {
        $this->sheet->mergeCells($cells);
    }

    public function borderBottomCellsRange($col1, $lig1, $col2, $lig2, array $array)
    {
        $color = $array['color'];
        if (0 === mb_strpos($color, '#')) {
            $color = mb_substr($color, 1, mb_strlen($color));
        }

        $cell1 = Coordinate::stringFromColumnIndex($col1) . $lig1;
        $cell2 = Coordinate::stringFromColumnIndex($col2) . $lig2;
        $this->sheet->getStyle($cell1 . ':' . $cell2)->getBorders()->getBottom()->setBorderStyle($array['size'])->getColor()->setARGB('FF' . $color);
    }

    public function getColumnsAutoSize(string $depart, string $fin)
    {
        foreach (range($depart, $fin) as $columnID) {
            $this->sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    public function getColumnsAutoSizeInt(int $depart, int $fin)
    {
        for ($columnID = $depart; $columnID <= $fin; $columnID++) {
            $this->sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnID))->setAutoSize(true);
        }
    }

    public function setSpreadsheet(Spreadsheet $sheet): void
    {
        $this->spreadsheet = $sheet;
//       if ($mcc === true) {
//           foreach ($this->spreadsheet->getAllSheets() as $sh) {
//               $sh->setShowGridlines(false);
//               //$sh->getProtection()->setSheet(true);
//           }
//       }
    }

    public function genereFichier($name)
    {
        $this->pageSetup($name);
        $writer = new Xlsx($this->spreadsheet);

        return new StreamedResponse(
            static function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' . $name . '.xlsx"',
            ]
        );
    }

    public function pageSetup($name): void
    {
        $this->spreadsheet->getProperties()->setTitle($name);
        $this->spreadsheet->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $this->spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $this->spreadsheet->getActiveSheet()->setShowGridlines(true); //affichage de la grille
        $this->spreadsheet->getActiveSheet()->setPrintGridlines(true); //affichage de la grille
        $this->spreadsheet->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(
            1,
            1
        ); //ligne a répéter en haut
        $this->spreadsheet->getActiveSheet()->getHeaderFooter()
            ->setOddHeader('&C&HDocument généré depuis ORéBUT');
        $this->spreadsheet->getActiveSheet()->getHeaderFooter()
            ->setOddFooter('&L&B' . $this->spreadsheet->getProperties()->getTitle() . '&RPage &P of &N');
    }

    public function createFromTemplate(string $fichier): Spreadsheet
    {
        $inputFileType = 'Xlsx'; // Xlsx - Xml - Ods - Slk - Gnumeric - Csv
        $inputFileName = $this->dir . $fichier;

        /**  Create a new Reader of the type defined in $inputFileType  **/
        $reader = IOFactory::createReader($inputFileType);

        /**  Load $inputFileName to a Spreadsheet Object  **/
        return $reader->load($inputFileName);
    }

    public function orientationCellXY(int $col, int $ligne, string $orientation)
    {
        $cell1 = Coordinate::stringFromColumnIndex($col) . $ligne;
        switch ($orientation) {
            case 'vertical':
                $this->sheet->getStyle($cell1)->getAlignment()->setTextRotation(90);
                $this->sheet->getStyle($cell1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                break;
        }
    }

    public function insertNewRowBefore(int $ligne): void
    {
        $this->sheet->insertNewRowBefore($ligne);
    }

    public function removeRow(int $ligne): void
    {
        $this->sheet->removeRow($ligne);
    }

    public function copyFromCellToCell(string $source, string $dest): void
    {
        $this->sheet->setCellValue($dest, $this->sheet->getCell($source)->getValue());
    }

    public function removeColumn(string $column, int $nbCol): void
    {
        $this->sheet->removeColumn($column, $nbCol);
    }

    public function getRowAutosize(int $ligne): void
    {
        $this->sheet->getRowDimension($ligne)->setRowHeight(-1);
    }
}
