<?php

namespace App\Controller;

use App\Repository\DpeParcoursRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OffreController extends BaseController
{
    #[Route('/offre', name: 'app_offre')]
    public function index(
        Request               $request,
        DpeParcoursRepository $dpeParcoursRepository,
    ): Response
    {
        // récupérer l'ensemble des formations et des parcours associés, pour chacun l'état du DPE courant
        $allParcours = $dpeParcoursRepository->findByCampagneCollecte($this->getCampagneCollecte());
        $tFormations = [];
        foreach ($allParcours as $parcours) {
            $formation = $parcours->getParcours()?->getFormation();
            $idFormation = $formation?->getId();
            if ($idFormation === null) {
                continue;
            }
            if (!array_key_exists($idFormation, $tFormations)) {
                $tFormations[$idFormation]['formation'] = $formation;
                $tFormations[$idFormation]['dpeParcours'] = [];
            }
            $tFormations[$idFormation]['dpeParcours'][] = $parcours;
        }

        // Construire les listes de filtres disponibles à partir des données
        $types = [];
        $composantes = [];
        $villes = [];
        foreach ($tFormations as $row) {
            $f = $row['formation'];
            $types[$f->getTypeDiplome()?->getLibelle() ?? ''] = true;
            $composantes[$f->getComposantePorteuse()?->getLibelle() ?? ''] = true;
            if ($f->isHasParcours() === false) {
                //  $ville = ($f->getLocalisationMention()->first())?->getLibelle();
                $ville = 'test';
                $villes[$ville ?? ''] = true;
            } else {
                // si RF, les villes sont gérées au niveau des parcours; on ne les agrège pas ici pour simplifier le POC
            }
        }
        $types = array_values(array_filter(array_keys($types)));
        sort($types);
        $composantes = array_values(array_filter(array_keys($composantes)));
        sort($composantes);
        $villes = array_values(array_filter(array_keys($villes)));
        sort($villes);

        // Lire les filtres de la requête
        $q = trim((string)$request->query->get('q', ''));
        $type = (string)$request->query->get('type', '');
        $comp = (string)$request->query->get('comp', '');
        $ville = (string)$request->query->get('ville', '');

        // Appliquer les filtres côté PHP sur le tableau tFormations
        if ($q || $type || $comp || $ville) {
            $tFormations = array_filter($tFormations, static function (array $row) use ($q, $type, $comp, $ville) {
                $f = $row['formation'];
                // Libellé
                if ($q !== '') {
                    $lib = (string)($f->getDisplayLong() ?? '');
                    if (mb_stripos($lib, $q) === false) {
                        return false;
                    }
                }
                // Type diplôme
                if ($type !== '') {
                    if (((string)($f->getTypeDiplome()?->getLibelle() ?? '')) !== $type) {
                        return false;
                    }
                }
                // Composante
                if ($comp !== '') {
                    if (((string)($f->getComposantePorteuse()?->getLibelle() ?? '')) !== $comp) {
                        return false;
                    }
                }
                // Ville (seulement lorsque pas de parcours)
                if ($ville !== '' && $f->isHasParcours() === false) {
                    $v = (string)(($f->getLocalisationMention()->first())?->getLibelle() ?? '');
                    if ($v !== $ville) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Déterminer si on ne doit renvoyer que le fragment du frame
        $frameId = $request->headers->get('Turbo-Frame');
        $isFrame = $frameId === 'offre_table';

        $params = [
            'tFormations' => $tFormations,
            'filters' => [
                'q' => $q,
                'type' => $type,
                'comp' => $comp,
                'ville' => $ville,
            ],
            'choices' => [
                'types' => $types,
                'composantes' => $composantes,
                'villes' => $villes,
            ],
        ];

        if ($isFrame) {
            // Répondre avec un markup contenant le <turbo-frame id="offre_table"> attendu
            return $this->render('offre/_table_frame.html.twig', $params);
        }

        return $this->render('offre/index.html.twig', $params);
    }
}
