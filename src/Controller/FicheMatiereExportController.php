<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereExportController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\MyGotenbergPdf;
use App\Entity\CampagneCollecte;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Message\Export;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class FicheMatiereExportController extends AbstractController
{
    public function __construct(
        private readonly MyGotenbergPdf $myPdf
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
        return $this->myPdf->render(
            'pdf/ficheMatiereAll.html.twig',
            [
                'formation' => $parcours->getFormation(),
                'parcours' => $parcours,
                'fiches' => $parcours->getFicheMatieres(),
                'typeDiplome' => $parcours->getFormation()?->getTypeDiplome(),
                'titre' => 'Fiches EC/matières ',
            ],
            'FichesMatieres' . $parcours->getDisplay()
        );
    }

    #[Route('/fiche-matiere/export/zip/{parcours}', name: 'fiche_matiere_export_zip')]
    public function exportFichesMatieresZip(
        MessageBusInterface          $messageBus,
        Parcours $parcours): Response
    {
        $messageBus->dispatch(new Export(
            $this->getUser()?->getId(),
            'zip-fiches_matieres',
            [$parcours->getId()]
        ));

        return JsonReponse::success('Les documents sont en cours de génération, vous recevrez un mail lorsque les documents seront prêts');
    }

    #[Route('/fiche-matiere/export/generique/xlsx', name: 'fiche_matiere_export_generique_xlsx')]
    public function getExportGeneriqueXlsx(
        EntityManagerInterface $em,
        Request $request
    ){
        [$fieldValueArray, $parcoursData, $campagneCollecte] = $this->checkExportGeneriqueData($em, $request);
        $this->checkFieldsAreSupported($fieldValueArray);

        // On trie les colonnes dans un certain ordre
        usort($fieldValueArray, 
            fn($f1, $f2) => $this->getFieldOrderForExportGenerique()[$f1] 
            <=> $this->getFieldOrderForExportGenerique()[$f2]
        );

        $parcoursData = array_map(fn($p) => $p->getId(), $parcoursData);

        $finalData = $em->getRepository(FicheMatiere::class)
            ->findByParcoursRangeForExport($parcoursData, $campagneCollecte);

        $finalData = array_map(
            function($fiche) use ($fieldValueArray){
                return array_merge(...array_map(
                    fn($field) => [$this->mapFicheMatiereExportWithValues($field, $fiche, 'xlsx')['value']]
                    , $fieldValueArray
                ));
            }, $finalData
        );

        $headersExcel = array_map(function ($field){
            return [
                $this->mapFicheMatiereExportWithValues($field, null, 'xlsx')['libelle']
            ];
        }, $fieldValueArray);

        $headersExcel = array_merge(...$headersExcel);

        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'
        ];

        $spreadSheet = new Spreadsheet();
        $activeWS = $spreadSheet->getActiveSheet();
        $activeWS->fromArray($headersExcel);
        $activeWS->fromArray($finalData, startCell: 'A2');

        foreach($columns as $col){
            $activeWS->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadSheet);
        $now = new DateTime();
        $dateFormat = $now->format('d-m-Y_H-i');

        $filename = "export_generique_excel_fiche_matiere_{$dateFormat}";

        return new StreamedResponse(
            function() use ($writer){
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment;filename=\"{$filename}\"",
            ]
        );
    }

    private function checkExportGeneriqueData(
        EntityManagerInterface $em,
        Request $request,
    ){
        $campagneCollecte = $request->query->get('campagneCollecte', 2);
        $campagneCollecte = $em->getRepository(CampagneCollecte::class)
            ->findOneById($campagneCollecte)
            ?? $em->getRepository(CampagneCollecte::class)->findOneBy(['defaut' => true]);

        $parcoursData = [];
        $parcoursIdArray = $request->query->all()['parcoursIdArray'] ?? [];
        if(isset($parcoursIdArray[0]) && $parcoursIdArray[0] === 'all'){
            $parcoursData = $em->getRepository(Parcours::class)->findByCampagneCollecte($campagneCollecte);
        }
        else {
            $parcoursIdArray = array_map(fn($id) => (int)$id, $parcoursIdArray);
            $parcoursData = $em->getRepository(Parcours::class)->findById($parcoursIdArray);
        }

        if(count($parcoursData) < 1){
            throw $this->createNotFoundException('Aucun parcours sélectionné.');
        }

        $fieldValueArray = $request->query->all()['fieldValueArray'] ?? [];
        // Vérification sur les champs demandés (non vide)
        if(count($fieldValueArray) === 0){
            throw $this->createNotFoundException('Aucun champ précisé.');
        }

        return [$fieldValueArray, $parcoursData, $campagneCollecte];
    }

    private function mapFicheMatiereExportWithValues(
        string $fieldValue,
        ?FicheMatiere $ficheMatiere,
        string $exportType = 'pdf'
    ){  
        switch($fieldValue){
            case 'fmId':
                return [
                    'libelle' => "Id",
                    'value' => $ficheMatiere?->getId() ?? ""
                ];
                break;
            case 'fmLibelle':
                return [
                    'libelle' => "Fiche EC/matière",
                    'value' => $ficheMatiere?->getLibelle() ?? ""
                ];
                break;
            case 'fmReferent':
                return [
                    'libelle' => "Référent",
                    'value' => $ficheMatiere?->getResponsableFicheMatiere()?->getDisplay() ?? ""
                ];
                break;
            case 'fmIsComplet':
                return [
                    'libelle' => "Complet ?",
                    'value' => $ficheMatiere?->remplissageBrut()?->isFull() ? 'Complet' : 'Incomplet'
                ];
                break;
            case 'fmNbUtilisee':
                return [
                    'libelle' => "Utilisée ?",
                    'value' => $ficheMatiere?->getElementConstitutifs()?->count() ?? ""
                ];
                break;
            case 'fmParcoursPorteur':
                return [
                    'libelle' => "Parcours porteur",
                    'value' => $ficheMatiere?->isHorsDiplome() 
                        ? 'Hors diplôme'
                        : $ficheMatiere?->getParcours()?->getLibelle() ?? ""
                ];
                break;
            case 'fmFormation':
                return [
                    'libelle' => "Formation",
                    'value' => $ficheMatiere?->isHorsDiplome()
                        ? 'Hors diplôme'
                        : $ficheMatiere?->getParcours()?->getFormation()?->getDisplayLong() ?? ""
                ];
                break;
        }
    }

    private function getFieldOrderForExportGenerique(){
        return [
            'fmId' => 1,
            'fmLibelle' => 2,
            'fmReferent' => 3,
            'fmIsComplet' => 4,
            'fmNbUtilisee' => 5,
            'fmParcoursPorteur' => 6,
            'fmFormation' => 7,
        ];
    }

    private function checkFieldsAreSupported(array $fieldValueArray){
        // Vérifications sur les champs demandés (les champs sont corrects)
        $fieldsAreCompatible = array_reduce(
            $fieldValueArray,
            fn($previous, $f) => $previous && array_key_exists($f, $this->getFieldOrderForExportGenerique()),
            true
        );
        if(!$fieldsAreCompatible){
            throw $this->createNotFoundException("Un des champs demandés n'est pas pris en charge.");
        }
    }
}
