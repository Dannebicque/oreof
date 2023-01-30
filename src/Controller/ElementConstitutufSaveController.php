<?php

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Enums\ModaliteEnseignementEnum;
use App\Enums\RythmeFormationEnum;
use App\Repository\ComposanteRepository;
use App\Repository\VilleRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ElementConstitutufSaveController extends BaseController
{
    #[Route('/ec/save/{ec}', name: 'app_ec_save')]
    public function save(
        EntityManagerInterface $em,
        UpdateEntity $updateEntity,
        Request $request,
        ElementConstitutif $ec
    ) {
        //todo: check si bonne formation...
        $data = JsonRequest::getFromRequest($request);
        switch ($data['action']) {
            case 'yesNo':
                $rep = $updateEntity->saveYesNo($ec, $data['field'], $data['value']);
                return $this->json($rep);
            case 'textarea':
            case 'selectWithoutEntity':
                $rep = $updateEntity->saveField($ec, $data['field'], $data['value']);
                return $this->json($rep);
            case 'float':
                $rep = $updateEntity->saveField($ec, $data['field'], (float)$data['value']);
                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField($ec, 'modalitesEnseignement', ModaliteEnseignementEnum::from($data['value']));
                return $this->json($rep);
            case 'rythmeFormation':
                $rep = $updateEntity->saveField($ec, 'rythmeFormation', RythmeFormationEnum::from($data['value']));
                return $this->json($rep);
            case 'int':
                $rep = $updateEntity->saveField($ec, $data['field'], (int)$data['value']);
                return $this->json($rep);
            case 'array':
                if ($data['isChecked'] === true) {
                    $rep = $updateEntity->addToArray($ec, $data['field'], $data['value']);
                } else {
                    $rep = $updateEntity->removeToArray($ec, $data['field'], $data['value']);

                }

                return $this->json($rep);
        }
    }


}
