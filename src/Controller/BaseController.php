<?php

namespace App\Controller;

use App\Entity\Constantes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    public function addFlashBag(string $niveau, string $message): void
    {
        switch ($niveau) {
            case Constantes::FLASHBAG_INFO:
                $this->toast(Constantes::FLASHBAG_INFO, $message, 'Information');
                break;
            case Constantes::FLASHBAG_SUCCESS:
                $this->toast(Constantes::FLASHBAG_SUCCESS, $message, 'Succès');
                break;
            case Constantes::FLASHBAG_NOTICE:
                $this->toast(Constantes::FLASHBAG_NOTICE, $message, 'Attention');
                break;
            case Constantes::FLASHBAG_ERROR:
                $this->toast(Constantes::FLASHBAG_ERROR, $message, 'Erreur');
                break;
            default:
                $this->toast(Constantes::FLASHBAG_ERROR, 'Clé inexistante', 'Erreur');
        }
    }

    protected function toast(string $type, string $text, ?string $title = null): void
    {
        $this->addFlash('toast', [
            'type' => $type,
            'text' => $text,
            'title' => $title,
        ]);
    }
}
