<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file src/Twig/Components/FaqEditor.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 2026-05-11
 */

namespace App\Twig\Components;

use App\Entity\Faq;
use App\Form\FaqType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('faq_editor')]
class FaqEditor extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Faq $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        // On crée le formulaire en lui passant les données initiales
        return $this->createForm(FaqType::class, $this->initialFormData);
    }
}
