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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

class MyDomPdf
{
    protected array $options;
    private string $basePath;

    public function __construct(
        protected Environment $twig,
        KernelInterface $kernel
    )
    {
        $this->basePath = $kernel->getProjectDir().'/public';
    }

    public function render(string $template, array $context = [], string $name = 'fichier', array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
        $html = $this->generateHtml($template, $context);

        $domOptions = new Options();
        $domOptions->set('isRemoteEnabled', $this->options['isRemoteEnabled']);
        $domOptions->set('chroot', $this->basePath);
        $dompdf = new Dompdf($domOptions);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->stream($this->valideName($name), ["Attachment" => $this->options['attachment']]);
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
        if (!str_contains($name, '.pdf')) {
            $name .= '.pdf';
        }

        return Tools::FileName($name);
    }
}



