<?php

namespace App\Controller;

use App\Classes\UpdateEntity;
use App\Entity\ElementConstitutif;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\LangueRepository;
use App\Repository\TypeEnseignementRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElementConstitutufSaveController extends BaseController
{
    #[Route('/ec/save/{ec}', name: 'app_ec_save')]
    public function save(
        TypeEnseignementRepository $typeEnseignementRepository,
        LangueRepository $langueRepository,
        EntityManagerInterface $em,
        UpdateEntity $updateEntity,
        Request $request,
        ElementConstitutif $ec
    ): Response {
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
            case 'langue':
                $rep = $updateEntity->saveCheckbox($ec, $data['field'], $data['value'],
                    $data['isChecked'],
                    $langueRepository);

                return $this->json($rep);
            case 'typeEnseignement':
                $rythme = $typeEnseignementRepository->find($data['value']);
                $rep = $updateEntity->saveField($ec, 'typeEnseignement', $rythme);

                return $this->json($rep);
            case 'modalitesEnseignement':
                $rep = $updateEntity->saveField($ec, 'modaliteEnseignement',
                    ModaliteEnseignementEnum::from($data['value']));

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
        return $this->json(['error' => 'action inconnue']);

    }


}
