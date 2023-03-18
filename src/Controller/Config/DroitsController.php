<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Config/DroitsController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Config;

use App\Entity\Domaine;
use App\Entity\Role;
use App\Enums\CentreGestionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleNiveauEnum;
use App\Form\DomaineType;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DroitsController extends AbstractController
{
    #[Route('/administration/droits/access', name: 'app_droits_access')]
    public function index(
        RoleRepository $roleRepository
    ): Response {
        return $this->render('config/droits/index.html.twig');
    }

    #[Route('/administration/droits/access/liste', name: 'app_droits_access_liste')]
    public function liste(
        RoleRepository $roleRepository
    ): Response {
        return $this->render('config/droits/_liste.html.twig', [
            'centres' => RoleNiveauEnum::cases(),
            'permissions' => PermissionEnum::cases(),
            'roles' => $roleRepository->findAll(),
        ]);
    }

    #[Route('/administration/droits/access/new', name: 'app_droits_access_new')]
    public function new(
        Request $request,
        RoleRepository $roleRepository
    ): Response {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role, [
            'action' => $this->generateUrl('app_droits_access_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->save($role, true);

            return $this->json(true);
        }

        return $this->render('config/droits/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/droits/access/{id}/edit', name: 'app_droits_access_edit')]
    public function edit(
        Request $request,
        RoleRepository $roleRepository,
        Role $role,
    ): Response {
        $form = $this->createForm(RoleType::class, $role, [
            'action' => $this->generateUrl('app_droits_access_edit', ['id' => $role->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->save($role, true);

            return $this->json(true);
        }

        return $this->render('config/droits/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/administration/droits/access/sauvegarder', name: 'app_droits_access_sauvegarder')]
    public function sauvegarderDroits(
        RoleRepository $roleRepository,
        Request $request
    ): Response {
        $codeRole = JsonRequest::getValueFromRequest($request, 'role');
        $code = JsonRequest::getValueFromRequest($request, 'code');
        $checked = JsonRequest::getValueFromRequest($request, 'checked');

        $role = $roleRepository->findOneBy(['code_role' => $codeRole]);

        if ($role !== null) {
            $droits = $role->getDroits();
            if ($checked === true) {
                $droits[] = $code;
            } else {
                $droits = array_diff($droits, [$code]);
            }
            $role->setDroits($droits);
            $roleRepository->save($role, true);

            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/administration/droits/access/{id}', name: 'app_droits_access_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Role $role,
        RoleRepository $roleRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $role->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $roleRepository->remove($role, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
