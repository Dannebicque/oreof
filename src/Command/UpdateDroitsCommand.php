<?php

namespace App\Command;

use App\Classes\GetDpeParcours;
use App\Entity\Profil;
use App\Entity\UserProfil;
use App\Enums\CentreGestionEnum;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\ProfilRepository;
use App\Repository\RoleRepository;
use App\Repository\UserCentreRepository;
use App\Repository\UserProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-droits',
    description: 'Add a short description for your command',
)]
class UpdateDroitsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoleRepository         $roleRepository,
        private UserCentreRepository   $userCentreRepository,
        private ProfilRepository       $profilRepository,
        private UserProfilRepository   $profilUserRepository,
        private FormationRepository    $formationRepository,
        private ParcoursRepository     $parcoursRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // on supprimer les data de profil et de USerProfil
        $io->note('Suppression des données de profil et de UserProfil...');
        $this->profilUserRepository->deleteAll();
        $this->profilRepository->deleteAll();

        // on recrée les profils en se basant sur les rôles
        $io->note('Recréation des profils à partir des rôles...');
        $tabProfils = [];
        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $profil = new Profil();
            $profil->setLibelle($role->getLibelle());
            $profil->setCode($role->getCodeRole());

            if ($role->getCodeRole() === 'ROLE_CO_RESP_PARCOURS' || $role->getCodeRole() === 'ROLE_RESP_PARCOURS') {
                $profil->setCentre(CentreGestionEnum::CENTRE_GESTION_PARCOURS);
            } else {
                if ($role->getCentre() !== null) {
                    $profil->setCentre($role->getCentre());
                }
            }


            $profil->setOnlyAdmin($role->isOnlyAdmin());
            $tabProfils[$role->getCodeRole()] = $profil;
            $this->entityManager->persist($profil);

        }
        $this->entityManager->flush();

        // on recrée les UserProfil en se basant sur les UserRoles
        $io->note('Recréation des UserProfil à partir des UserRoles...');
        $userRoles = $this->userCentreRepository->findAll();
        foreach ($userRoles as $userRole) {
            foreach ($userRole->getDroits() as $droit) {
                if (array_key_exists($droit, $tabProfils) &&
                    ($droit !== 'ROLE_RESP_FORMATION' && $droit !== 'ROLE_RESP_PARCOURS' && $droit !== 'ROLE_CO_RESP_FORMATION' && $droit !== 'ROLE_CO_RESP_PARCOURS')) {
                    // on ne reprend que les droits principaux
                    $up = new UserProfil();
                    $up->setUser($userRole->getUser());
                    $up->setCampagneCollecte($userRole->getCampagneCollecte());
                    $up->setProfil($tabProfils[$droit]);

                    //selon le centre du droit ajouter composante, formation ou parcours
                    switch ($tabProfils[$droit]->getCentre()) {
                        case CentreGestionEnum::CENTRE_GESTION_COMPOSANTE:
                            $up->setComposante($userRole->getComposante());
                            break;
                        case CentreGestionEnum::CENTRE_GESTION_FORMATION:
                            $up->setFormation($userRole->getFormation());
                            break;
                        case CentreGestionEnum::CENTRE_GESTION_ETABLISSEMENT:
                            $up->setEtablissement($userRole->getEtablissement());
                            break;
                        case CentreGestionEnum::CENTRE_GESTION_PARCOURS:
                            throw new \Exception('To be implemented');
                            break;
                        case CentreGestionEnum::CENTRE_GESTION_NULL:
                            throw new \Exception('To be implemented');
                    }
                    $this->entityManager->persist($up);
                }
            }
        }

        // on ajoute les droits selon la formation ou le parcours
        $mentions = $this->formationRepository->findAll();
        foreach ($mentions as $mention) {
            if ($mention->getResponsableMention() !== null) {

                $up = new UserProfil();
                $up->setUser($mention->getResponsableMention());
                $up->setCampagneCollecte($mention->getDpe());
                $up->setProfil($tabProfils['ROLE_RESP_FORMATION']);
                $up->setFormation($mention);
                $this->entityManager->persist($up);
            }

            if ($mention->getCoResponsable() !== null) {

                $up = new UserProfil();
                $up->setUser($mention->getCoResponsable());
                $up->setCampagneCollecte($mention->getDpe());
                $up->setProfil($tabProfils['ROLE_CO_RESP_FORMATION']);
                $up->setFormation($mention);
                $this->entityManager->persist($up);
            }
        }

        $parcours = $this->parcoursRepository->findAll();
        foreach ($parcours as $parc) {
            $dpe = GetDpeParcours::getFromParcours($parc);

            if ($dpe === null) {
                $io->warning('DPE non trouvé pour le parcours : ' . $parc->getDisplay());
                continue;
            }
            if ($parc->getRespParcours() !== null) {
                $up = new UserProfil();
                $up->setUser($parc->getRespParcours());
                $up->setCampagneCollecte($dpe->getCampagneCollecte());
                $up->setProfil($tabProfils['ROLE_RESP_PARCOURS']);
                $up->setParcours($parc);
                $this->entityManager->persist($up);
            }

            if ($parc->getCoResponsable() !== null) {
                $up = new UserProfil();
                $up->setUser($parc->getCoResponsable());
                $up->setCampagneCollecte($dpe->getCampagneCollecte());
                $up->setProfil($tabProfils['ROLE_CO_RESP_PARCOURS']);
                $up->setParcours($parc);
                $this->entityManager->persist($up);

            }
        }


        $this->entityManager->flush();


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
