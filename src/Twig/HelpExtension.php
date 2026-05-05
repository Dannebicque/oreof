<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Twig/HelpExtension.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 29/04/2026 15:20
 */


namespace App\Twig;

use App\Entity\Help;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class HelpExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
    )
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('get_page_help', [$this, 'getPageHelp'])];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('parse_embeds', [$this, 'parseEmbeds'], ['is_safe' => ['html']]),
        ];
    }

    public function getPageHelp(string $routeSlug = null): ?Help
    {
        if (!$routeSlug) {
            return null;
        }

        $help = $this->em->getRepository(Help::class)->findOneBy(['routeSlug' => $routeSlug, 'isActive' => true]);
        if (!$help) {
            return null;
        }

        if ($help->getProfilsAutorises()->isEmpty() || $this->security->isGranted('ROLE_ADMIN')) {
            return $help;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return null;
        }

        $userProfilIds = [];
        foreach ($user->getUserProfils() as $userProfil) {
            $profil = $userProfil->getProfil();
            if ($profil !== null && $profil->getId() !== null) {
                $userProfilIds[$profil->getId()] = true;
            }
        }

        foreach ($help->getProfilsAutorises() as $profilAutorise) {
            if ($profilAutorise->getId() !== null && isset($userProfilIds[$profilAutorise->getId()])) {
                return $help;
            }
        }

        return null;
    }

    public function parseEmbeds(string $content): string
    {
        // syntaxe attendue dans le markdown: [embed](https://youtube.com/watch?v=xxx)
        // en html apres markdown: <a href="https://youtube.com/watch?v=xxx">embed</a>
        $pattern = '/<a href="([^"]+)">(embed|video)<\/a>/i';
        
        return preg_replace_callback($pattern, function ($matches) {
            $url = $matches[1];
            
            // Transformer les urls standards en versions embed
            $url = str_replace(
                ['watch?v=', 'youtu.be/', 'vimeo.com/'],
                ['embed/', 'youtube.com/embed/', 'player.vimeo.com/video/'],
                $url
            );

            return sprintf(
                '<div class="ratio ratio-16x9 my-3 rounded overflow-hidden shadow-sm"><iframe src="%s" allowfullscreen></iframe></div>',
                $url
            );
        }, $content);
    }
}