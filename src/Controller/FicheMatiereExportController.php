<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\MyPDF;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FicheMatiereExportController extends AbstractController
{
    public function __construct(
        private readonly MyPDF $myPdf
    )
    {
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/fiche-matiere/export/{id}', name: 'app_fiche_matiere_export')]
    public function export(FicheMatiere $ficheMatiere): Response
    {
        $formation = $ficheMatiere->getParcours()?->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        $typeDiplome = $formation->getTypeDiplome();

        return $this->myPdf::generePdf(
            'pdf/ec.html.twig',
            [
                'ficheMatiere' => $ficheMatiere,
                'formation' => $formation,
                'typeDiplome' => $typeDiplome,
                'bccs' => $bccs
            ],
            'dpe_fiche_matiere_'.$ficheMatiere->getLibelle()
        );
    }
}
