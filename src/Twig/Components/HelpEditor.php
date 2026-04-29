<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Twig/Components/HelpEditor.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 29/04/2026 15:16
 */


namespace App\Twig\Components;

use App\Entity\Help;
use App\Form\HelpType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('help_editor')]
class HelpEditor extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Help $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(HelpType::class, $this->initialFormData);
    }
}
