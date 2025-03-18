<?php

namespace App\Security\Voter;

use App\Classes\GetDpeParcours;
use App\Entity\Composante;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\User;
use App\Entity\UserCentre;
use App\Enums\PermissionEnum;
use App\Enums\PorteeEnum;
use App\Enums\RoleNiveauEnum;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\RoleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class GlobalVoter extends Voter
{
    private string $roleNiveau;
    private string $permission;
    private string $portee;
    private User|UserInterface $user;

    public function __construct(
        //        private WorkflowInterface       $dpeWorkflow,
        //        private WorkflowInterface       $parcoursWorkflow,
        private WorkflowInterface       $dpeParcoursWorkflow,
        private WorkflowInterface       $ficheWorkflow,
        private readonly Security       $security,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Attribute = CAN_{roleNiveau}_{Permission}_{portee}
        if (str_starts_with($attribute, 'CAN_')) {
            if ($this->decomposeAttribute($attribute) === false) {
                return false;
            }

            if (!in_array($this->roleNiveau, RoleNiveauEnum::getAvailableTypes())) {
                return false;
            }

            if (!in_array($this->permission, PermissionEnum::getAvailableTypes())) {
                return false;
            }

            if (!in_array($this->portee, PorteeEnum::getAvailableTypes())) {
                return false;
            }

            return true;
        }
        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $this->user = $token->getUser();

        if (
            !$subject instanceof DpeParcours &&
            !$subject instanceof Parcours &&
            !$subject instanceof Formation &&
            !$subject instanceof FicheMatiere &&
            $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // récupérer les centres de l'utilisateur
        // vérifier si un rôle compatible est présent sur au moins un centre de l'utilisateur
        // si non, accès interdit.
        // si oui, vérifier la portée du rôle, si besoin vérifier par hierarchie
        $roles = $this->roleRepository->findByPermission($this->getRoleFromAttribute()); // on récupère les rôles qui ont la permission demandée
        foreach ($this->user->getUserCentres()  as $centre) {
            if (array_intersect($centre->getDroits(), $roles) || $this->security->isGranted('ROLE_SES')) {
                // on a au moins un centre en commun, on vérifie la portée
                if ($this->portee === PorteeEnum::ALL->value) {
                    return true; //pas de portée, c'est OK
                }

                // on a une portée, on vérifie si le centre est dans la portée
                if ($this->portee === PorteeEnum::MY->value) {
                    // Soit formation ou composante, on vérifie si le centre est dans la portée
                    // soit par héritage

                    if ($subject instanceof Formation) {
                        if ($this->canAccessFormation($subject, $centre) === true) {
                            return true;
                        }
                    }

                    if ($subject instanceof FicheMatiere) {
                        if ($this->canAccessFicheMatiere($subject, $centre) === true) {
                            return true;
                        }
                    }

                    if ($subject instanceof Parcours) {
                        $dpeParcours = GetDpeParcours::getFromParcours($subject);
                        if ($dpeParcours !== null) {
                            if ($this->canAccessDpeParcours($dpeParcours, $centre) === true) {
                                return true;
                            }
                        }
                    }

                    if ($subject instanceof DpeParcours) {
                        if ($this->canAccessDpeParcours($subject, $centre) === true) {
                            return true;
                        }
                    }

                    if ($subject instanceof Composante) {
                        if ($this->canAccessComposante($subject, $centre)) {
                            //soit centre = composante et responsable ou dpe de la composante
                            return true;
                        }
                    }

                    if ($subject instanceof User) {
                        if ($this->canUserAccessComposante($subject, $centre)) {
                            //soit centre = composante et responsable ou dpe de la composante
                            return true;
                        }
                    }
                }
            }
        }
        return false;//pas défaut c'est non...
    }

    private function decomposeAttribute(string $attribute): bool
    {
        $tab = explode('_', $attribute);
        if (count($tab) !== 4) {
            return false;
        }

        $this->roleNiveau = $tab[1];
        $this->permission = $tab[2];
        $this->portee = $tab[3];

        return true;
    }

    private function getRoleFromAttribute(): string
    {
        return 'ROLE_' . $this->roleNiveau . '_' . $this->permission . '_' . $this->portee;
    }

    private function canAccessFormation(Formation $subject, mixed $centre): bool
    {
        $canOpen = true;
        $canEdit =
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_PARCOURS ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION;

        $centre = ($centre->getFormation() === $subject && ($subject->getCoResponsable()?->getId() === $this->user || $subject->getResponsableMention()?->getId() === $this->user))|| $centre->getComposante() === $subject->getComposantePorteuse() || $this->security->isGranted('ROLE_SES');

        return ($canEdit || $canOpen) && $centre;
    }

    //    private function canAccessParcours(Parcours $subject, mixed $centre): bool
    //    {
    //        $canEdit = false;
    //        if ($subject->getRespParcours() === $this->user || $subject->getCoResponsable() === $this->user) {
    //            $canEdit = $this->parcoursWorkflow->can($subject, 'autoriser') || $this->parcoursWorkflow->can($subject, 'valider_parcours');
    //        }
    //
    //        if ($subject->getFormation()?->getResponsableMention() === $this->user || $subject->getFormation()?->getCoResponsable() === $this->user) {
    //            $canEdit = $this->parcoursWorkflow->can($subject, 'autoriser') ||
    //                $this->parcoursWorkflow->can($subject, 'valider_parcours') ||
    //                $this->parcoursWorkflow->can($subject, 'valider_rf') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'autoriser') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_rf');
    //        }
    //
    //        if (
    //            $subject->getFormation()?->getComposantePorteuse() === $centre->getComposante() &&
    //            ($centre->getComposante()?->getResponsableDpe() === $this->user || $subject->getFormation()?->getComposantePorteuse() === $centre->getComposante())) {
    //            //todo: filtre pas si les bons droits... Edit ou lecture ?
    //            $canEdit = $this->parcoursWorkflow->can($subject, 'autoriser') ||
    //                $this->parcoursWorkflow->can($subject, 'valider_parcours') ||
    //                $this->parcoursWorkflow->can($subject, 'valider_rf') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'autoriser') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_rf') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_dpe_composante') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_conseil');
    //        }
    //
    //        if ($this->security->isGranted('ROLE_SES')) {
    //            $canEdit =
    //                $this->dpeWorkflow->can($subject->getFormation(), 'autoriser') ||
    //                $this->parcoursWorkflow->can($subject, 'autoriser') ||
    //                $this->parcoursWorkflow->can($subject, 'valider_parcours') ||
    //                $this->dpeParcoursWorkflow->can($subject->getDpeParcours()->first(), 'valider_ouverture_sans_cfvu') ||
    //                $this->parcoursWorkflow->can($subject, 'valider_rf') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_rf') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_dpe_composante') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_conseil') ||
    //                $this->dpeWorkflow->can($subject->getFormation(), 'valider_central');
    //        }
    //
    //        return $canEdit;
    //    }

    private function canAccessDpeParcours(DpeParcours $subject, mixed $centre): bool
    {
        $parcours = $subject->getParcours();
        if ($parcours === null) {
            return false;
        }
        $canEdit = false;
        $canOpen = false;
        if ($parcours->getRespParcours() === $this->user || $parcours->getCoResponsable() === $this->user) {
            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'autoriser') || $this->dpeParcoursWorkflow->can($subject, 'valider_parcours');
            $canOpen = true;
        }

        if ($parcours->getFormation()?->getResponsableMention() === $this->user || $parcours->getFormation()?->getCoResponsable() === $this->user) {
            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
              //  $this->dpeParcoursWorkflow->can(subject, 'valider_ouverture_sans_cfvu') || todo: a mettre dès l'ouverture
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf');
            $canOpen = true;
        }

        if (
            $parcours->getFormation()?->getComposantePorteuse() === $centre->getComposante() &&
            ($centre->getComposante()?->getResponsableDpe() === $this->user || $parcours->getFormation()?->getComposantePorteuse() === $centre->getComposante())) {
            //todo: filtre pas si les bons droits... Edit ou lecture ?
            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
                //$this->dpeParcoursWorkflow->can(subject, 'valider_ouverture_sans_cfvu') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf');
            $canOpen = true;
        }

        if ($this->security->isGranted('ROLE_SES')) {
            $canEdit =
                $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_ouverture_sans_cfvu') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_central');
            $canOpen = true;
        }


        return $canEdit || $canOpen;
    }

    private function canAccessComposante(Composante $subject, mixed $centre): bool
    {
        return true;
    }

    private function canUserAccessComposante(User $subject, UserCentre $centre): bool
    {
        if (
            $this->roleNiveau === RoleNiveauEnum::COMPOSANTE->value && //todo: ou un niveau supérieur
            $this->user === $subject && $centre->getComposante() !== null
        ) {
            return true;
        }

        return false;
    }

    private function canAccessFicheMatiere(FicheMatiere $subject, mixed $centre): bool
    {
        $canEdit = false;
        if ($subject->getResponsableFicheMatiere() === $this->user ||
            $subject->getParcours()?->getRespParcours() === $this->user ||
            $subject->getParcours()?->getCoResponsable() === $this->user ||
            $subject->getParcours()?->getFormation()?->getResponsableMention() === $this->user ||
            $subject->getParcours()?->getFormation()?->getCoResponsable() === $this->user ||
            $subject->getParcours()?->getFormation()?->getComposantePorteuse()?->getResponsableDpe() === $this->user ||
            ($subject->getParcours()?->getFormation()?->getComposantePorteuse() === $centre->getComposante() &&
                in_array('Gestionnaire', $centre->getDroits()))
        ) {
            $canEdit = $this->ficheWorkflow->can($subject, 'valider_fiche_compo');
        }

        if ($this->security->isGranted('ROLE_SES')) {
            $canEdit =
                $this->ficheWorkflow->can($subject, 'valider_fiche_compo') ||
                $this->ficheWorkflow->can($subject, 'valider_fiche_ses');
        }

        return $canEdit;
    }
}
