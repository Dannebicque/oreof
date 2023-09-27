<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\MyDomPdf;
use App\Classes\MyPDF;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use Dompdf\Dompdf;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FicheMatiereExportController extends AbstractController
{
    public function __construct(
        private readonly MyDomPdf $myPdf
    ) {
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
        if ($ficheMatiere->isHorsDiplome() === false) {
            $formation = $ficheMatiere->getParcours()?->getFormation();
            if ($formation === null) {
                throw new RuntimeException('Formation non trouvée');
            }
            $typeDiplome = $formation->getTypeDiplome();
        } else {
            $typeDiplome = null;
            $formation = null;
        }

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }



        return $this->myPdf->render(
            'pdf/ec.html.twig',
            [
                'ficheMatiere' => $ficheMatiere,
                'formation' => $formation,
                'typeDiplome' => $typeDiplome,
                'bccs' => $bccs,
                'titre' => 'Fiche EC/matière ' . $ficheMatiere->getLibelle(),
            ],
            'dpe_fiche_matiere_' . $ficheMatiere->getLibelle()
        );
    }

    #[Route('/fiche-matiere/export/all/{parcours}', name: 'fiche_matiere_export_all')]
    public function exportFichesMatieres(Parcours $parcours): Response
    {
        $html = $this->renderView('pdf/ficheMatiereAll.html.twig', [
            'formation' => $parcours->getFormation(),
            'parcours' => $parcours,
            'fiches' => $parcours->getFicheMatieres(),
            'typeDiplome' => $parcours->getFormation()?->getTypeDiplome(),
        ]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $dompdf->stream('FichesMatieres' . $parcours->getLibelle(), ["Attachment" => true]);
    }

    #[Route('/fiche-matiere/export/zip/{parcours}', name: 'fiche_matiere_export_zip')]
    public function exportFichesMatieresZip(Parcours $parcours): Response
    {
    }
}
