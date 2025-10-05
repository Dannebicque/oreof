<?php
// src/Twig/TranslationDebugExtension.php
namespace App\Twig;

use \Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationDebugExtension extends AbstractExtension
{
    private TranslatorInterface $translator;
    private Security $security;

    public function __construct(TranslatorInterface $translator, Security $security)
    {
        $this->translator = $translator;
        $this->security = $security;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('trans_debug', $this->transDebug(...), ['is_safe' => ['html']]),
        ];
    }

    public function transDebug($key, array $params = [], $domain = null)
    {
        $translated = $this->translator->trans($key, $params, $domain);
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return sprintf(
                '%s <i class="fas fa-info-circle"
            data-controller="tooltip"
                       data-tooltip-placement-value="bottom"
                       title="%s"
            ></i>',
                $translated,
                htmlspecialchars($key)
            );
        }
        return $translated;
    }
}
