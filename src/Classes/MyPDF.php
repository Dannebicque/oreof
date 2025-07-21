<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/MyPDF.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 22/02/2023 09:40
 */

namespace App\Classes;

use App\Utils\Tools;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MyPDF
{
    protected static array $options = [];

    protected static Environment $templating;

    private static Pdf $pdf;

    private static string $basePath;

    /**
     * MyPDF constructor.
     */
    public function __construct(
        KernelInterface $kernel,
        Environment     $templating,
        Pdf             $pdf
    ) {
        self::$templating = $templating;
        self::$pdf = $pdf;
        self::$options['enable-local-file-access'] = true; // https://yusufbiberoglu.medium.com/symfony-5-knpsnappybundle-wkhtmltopdf-setup-and-example-with-an-explanation-of-possible-errors-a890dbca238a

        $date = new DateTime('now');
        self::$basePath = $kernel->getProjectDir() . '/public';
        self::setFooter([
            'footer-html' => self::$templating->render('pdf/footer.html.twig', [
                'date' => $date,
            ]),
//            'footer-left' => "Document généré depuis OReOF le " . $date->format('d/m/Y H:i') . '.',
//            'footer-right' => '[page] / [topage]',
//            'footer-center' => '',
        ]);
    }

    public static function addOptions(string $key, string $value): void
    {
        // options disponibles : https://wkhtmltopdf.org/usage/wkhtmltopdf.txt
        self::$options[$key] = $value;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function generePdf(string $template, array $data, string $name): PdfResponse
    {
        return self::genereOutputPdf($template, $data, $name);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function genereAndSavePdf(
        string $template,
        array  $data,
        string $name,
        string $dir
    ): string {
        $output = self::genereOutputPdf($template, $data, $name);

        file_put_contents($dir . Tools::FileName($name) . '.pdf', $output);

        return Tools::FileName($name) . '.pdf';
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private static function genereOutputPdf(string $template, array $data, string $name): PdfResponse
    {
        $name = Tools::slug($name);
        $opts = array_merge(
            $data,
            ['baseUrl' => self::$basePath]
        );
        self::setHeaderHtml(
            'pdf/header.html.twig',
            $opts
        );

        $html = self::$templating->render($template, $opts);

        if ('.pdf' !== mb_substr($name, -4)) {
            $name .= '.pdf';
        }

        return new PdfResponse(
            self::$pdf->getOutputFromHtml($html, self::$options),
            $name
        );
    }

    public static function setFooter(array $data = []): void
    {
        foreach ($data as $key => $d) {
            self::addOptions($key, $d);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public static function setFooterHtml(string $template, array $data = []): void
    {
        self::addOptions('footer-html', self::$templating->render($template, $data));
    }

    public static function setHeaderHtml(string $template, array $data = []): void
    {
        self::addOptions('header-html', self::$templating->render($template, $data));
    }
}
