<?php

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Entity\Formation;
use App\Enums\ModaliteEnseignementEnum;
use App\Enums\RythmeFormationEnum;
use App\Repository\SiteRepository;
use App\Utils\JsonRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FormationSaveController extends BaseController
{
    #[Route('/formation/save/{formation}', name: 'app_formation_save')]
    public function save(
        UpdateEntity $updateEntity,
        SiteRepository $siteRepository,
        Request $request,
        Formation $formation
    ) {
        //todo: check si bonne formation...
        $data = JsonRequest::getFromRequest($request);
        switch ($data['action']) {
            case 'site':
                $rep = $updateEntity->saveCheckbox($formation, 'site', $data['value'], $data['isChecked'],
                    $siteRepository);

                return $this->json($rep);
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($formation, $data['field'], $data['value']);
                return $this->json($rep);
            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($formation, $data['field'], $data['value']);
                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($formation, $data['field'], (float)$data['value']);
                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField($formation, 'modalitesEnseignement', ModaliteEnseignementEnum::from($data['value']));
                return $this->json($rep);
            case 'rythmeFormation':
                $rep = $updateEntity->saveField($formation, 'rythmeFormation', RythmeFormationEnum::from($data['value']));
                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($formation, $data['field'], (int)$data['value']);
                return $this->json($rep);
        }
    }

}
