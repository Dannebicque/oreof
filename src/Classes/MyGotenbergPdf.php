<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/MyDomPdf.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/08/2023 15:54
 */

namespace App\Classes;

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

    public function __construct(
        protected Environment     $twig,
        protected ClientInterface $client,
        KernelInterface           $kernel
    )
    {
        $this->basePath = $kernel->getProjectDir() . '/public';
    }

    public function render(string $template, array $context = [], string $name = 'fichier', array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
        $html = $this->generateHtml($template, $context);

        $request = Gotenberg::chromium('http://localhost:3000')
            ->assets(
                Stream::path($this->basePath.'/images/logo_urca.png'),
            )
            ->header(Stream::string('header.html', $this->getHeader($context['titre'])))
            ->footer(Stream::string('footer.html', $this->getFooter()))
            ->paperSize(8.27, 11.7)
            ->margins(1, 1, 0.8, 0.8)
            ->outputFilename($name)
            ->html(Stream::string('fichier.html', $html))
            ;

        // $response = $this->client->sendRequest($request);
        //$filename = Gotenberg::save($request, '/Users/davidannebicque/Sites/oreof/public/pdftests/', $this->client);
        $reponse = $this->client->sendRequest($request);

        return new Response($reponse->getBody()->getContents(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '.pdf"',
        ]);
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



