<?php

namespace App\Controller;

use App\Classes\MyPDF;
use App\Entity\ElementConstitutif;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElementConstitutifExportController extends AbstractController
{
    public function __construct(
        private readonly TypeDiplomeRegistry $typeDiplomeRegistry,
        private readonly MyPDF $myPdf)
    {
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    #[Route('/element/constitutif/export/{elementConstitutif}', name: 'app_element_constitutif_export')]
    public function export(ElementConstitutif $elementConstitutif): Response
    {
        $formation = $elementConstitutif->getParcours()->getFormation();
        if ($formation === null) {
            throw new \Exception('Formation non trouvée');
        }

        $bccs = [];
        foreach ($elementConstitutif->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()->getId()]['competences'][] = $competence;
        }

        $typeDiplome = $this->typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());

        return $this->myPdf::generePdf('pdf/ec.html.twig',
            [
                'elementConstitutif' => $elementConstitutif,
                'template' => $typeDiplome::TEMPLATE,
                'formation' => $formation,
                'typeDiplome' => $typeDiplome,
                'bccs' => $bccs

            ], 'dpe_ec_'.$elementConstitutif->getLibelle());
    }
}
