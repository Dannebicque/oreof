<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/MyDomPdf.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/08/2023 15:54
 */

namespace App\Classes;

use App\Entity\Parcours;
use App\Utils\Tools;
use Dompdf\Dompdf;
use Dompdf\Options;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class MyGotenbergPdf
{
    protected array $options;
    private string $basePath;

    private array $paperSizes = [
        'A4' => [8.3, 11.7],
        'A3' => [11.7, 16.5],
        'A2' => [16.5, 23.4],
    ];

    public function __construct(
        protected Environment     $twig,
        protected ClientInterface $client,
        KernelInterface           $kernel
    ) {
        $this->basePath = $kernel->getProjectDir() . '/public';
    }

    public function render(string $template, array $context = [], string $name = 'fichier', array $options = [])
    {
        $request = $this->calculPdf($template, $context, $name, $options);
        $reponse = $this->client->sendRequest($request);

        return new Response($reponse->getBody()->getContents(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '.pdf"',
        ]);
    }

    private function calculPdf(string $template, array $context = [], string $name = 'fichier', array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
        $html = $this->generateHtml($template, $context);

        if (array_key_exists('parcours', $context) && $context['parcours'] instanceof Parcours) {
            $parcours = $context['parcours'];
            //formation + mention + parcours + intitulé parcours
            $formation = $parcours->getFormation();
            $title = $formation->getDisplayLong() . '<br> Parcours : ' . $parcours->getDisplay() . '<br>' . $context['titre'];
        } else {
            $title = $context['titre'];
        }

        $request = Gotenberg::chromium('http://localhost:3000')
            ->emulatePrintMediaType()
            ->assets(
                Stream::path($this->basePath . '/images/logo_urca.png'),
                Stream::path($this->basePath . '/build/print.css'),
            )
            ->header(Stream::string('header.html', $this->getHeader($title)))
            ->footer(Stream::string('footer.html', $this->getFooter()))
            ->paperSize($this->paperSizes[$this->options['paperSize']][0], $this->paperSizes[$this->options['paperSize']][1])
            ->margins(1, 1, 0.8, 0.8)
            ->outputFilename($name);

        if ($this->options['landscape']) {
            $request->landscape();
        }

        return $request->html(Stream::string('fichier.html', $html));
    }

    public function renderAndSave(string $template, string $dir, array $context = [], string $name = 'fichier', array $options = []): string
    {
        $request = $this->calculPdf($template, $context, $name, $options);

        $filename = Gotenberg::save($request, $this->basePath . '/' . $dir, $this->client);

        return $filename;
    }

    private function generateHtml(string $template, array $context = [])
    {
        $context = array_merge($context, ['baseUrl' => $this->options['baseUrl']]);


        return $this->twig->render($template, $context);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'isRemoteEnabled' => true,
            'attachment' => true,
            'baseUrl' => $this->basePath,
            'landscape' => false,
            'paperSize' => 'A4',
        ]);
    }

    private function valideName(string $name): string
    {
        if (false === strpos($name, '.pdf')) {
            $name .= '.pdf';
        }

        return Tools::FileName($name);
    }

    private function getHeader(string $titre = ''): string
    {
        $imageData = file_get_contents($this->options['baseUrl'] . '/images/logo_urca.png');
        $base64Image = base64_encode($imageData);
        return $this->twig->render('pdf/header.html.twig', [
            'baseUrl' => $this->options['baseUrl'],
            'titre' => $titre,
            'image' => $base64Image
        ]);
    }

    private function getFooter(): string
    {
        return $this->twig->render('pdf/footer.html.twig', [
            'baseUrl' => $this->options['baseUrl'],

        ]);
    }
}
