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
            if (array_intersect($centre->getDroits(), $roles) || $this->security->isGranted('ROLE_ADMIN')) {
                // on a au moins un centre en commun, on vérifie la portée
                if ($this->portee === PorteeEnum::ALL->value) {
                    return true; //pas de portée, c'est OK
                }

                // on a une portée, on vérifie si le centre est dans la portée
                if ($this->portee === PorteeEnum::MY->value) {
                    // Soit formation ou composante, on vérifie si le centre est dans la portée
                    // soit par héritage
                    if ($subject instanceof Formation) {
                        if (
                            strtolower($this->permission) === PermissionEnum::MANAGE->value &&
                        $this->canManageFormation($subject, $centre) === true
                        ) {
                            return true;
                        }

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
                            if (strtolower($this->permission) === PermissionEnum::MANAGE->value && $this->canManageDpeParcours($dpeParcours, $centre) === true) {
                                return true;
                            }

                            if ($this->canAccessDpeParcours($dpeParcours, $centre) === true) {
                                return true;
                            }
                        }
                    }

                    if ($subject instanceof DpeParcours) {
                        if (strtolower($this->permission) === PermissionEnum::MANAGE->value && $this->canManageDpeParcours($subject, $centre) === true) {
                            return true;
                        }

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
        $canEdit =
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_PARCOURS ||
            $subject->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION;

        if ($subject->isHasParcours() === false) {
            // parcours par défaut
            $parcours = $subject->getParcours()->first();
            if ($parcours !== null && $parcours instanceof Parcours) {
                $dpeParcours = GetDpeParcours::getFromParcours($parcours);
                if ($dpeParcours !== null) {
                    $canEdit = $canEdit || $this->canAccessDpeParcours($dpeParcours, $centre);
                }
            }
        }

        $canCentre = ($centre->getFormation() === $subject && ($subject->getCoResponsable()?->getId() === $this->user->getId() || $subject->getResponsableMention()?->getId() === $this->user->getId()))|| $centre->getComposante() === $subject->getComposantePorteuse() || $this->security->isGranted('ROLE_ADMIN');

        return $canEdit && $canCentre;
    }

    private function canManageFormation(Formation $subject, mixed $centre): bool
    {
        $canEdit =
            $subject->getEtatReconduction() === TypeModificationDpeEnum::OUVERT || $subject->getEtatReconduction() === null;
        $canCentre = ($centre->getFormation() === $subject && ($subject->getCoResponsable()?->getId() === $this->user->getId() || $subject->getResponsableMention()?->getId() === $this->user->getId()))|| $centre->getComposante()?->getId() === $subject->getComposantePorteuse()?->getId() || $this->security->isGranted('ROLE_ADMIN');

        return $canEdit && $canCentre;
    }

    private function canAccessDpeParcours(DpeParcours $subject, mixed $centre): bool
    {
        $parcours = $subject->getParcours();
        if ($parcours === null) {
            return false;
        }
        $canEdit = false;
        if ($parcours->getRespParcours() === $this->user || $parcours->getCoResponsable() === $this->user) {
            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'autoriser') || $this->dpeParcoursWorkflow->can($subject, 'valider_parcours');
        }

        if ($parcours->getFormation()?->getResponsableMention() === $this->user || $parcours->getFormation()?->getCoResponsable() === $this->user) {

            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
              //  $this->dpeParcoursWorkflow->can(subject, 'valider_ouverture_sans_cfvu') || todo: a mettre dès l'ouverture
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf');
        }

        if (
            $parcours->getFormation()?->getComposantePorteuse() === $centre->getComposante() &&
            ($centre->getComposante()?->getResponsableDpe() === $this->user || $parcours->getFormation()?->getComposantePorteuse() === $centre->getComposante())) {
            //todo: filtre pas si les bons droits... Edit ou lecture ?
            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_dpe_composante') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_conseil') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf');
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $canEdit =
                $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_ouverture_sans_cfvu') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_dpe_composante') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_conseil') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_central');
        }


        return $canEdit;
    }

    private function canAccessComposante(Composante $subject, mixed $centre): bool
    {
        if (
            ($centre->getComposante() === $subject && (in_array('Gestionnaire', $centre->getDroits()) || in_array('Directeur', $centre->getDroits()) || $subject->getResponsableDpe() === $this->user)) || $this->security->isGranted('ROLE_ADMIN')
        ) {
            return true;
        }

        return false;
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
            $canEdit = $this->ficheWorkflow->can($subject, 'valider_fiche_compo') || $this->ficheWorkflow->can($subject, 'rouvrir_fiche_matiere');
        }

        $access = false;
        //cas hors diplôme, on vérifie si le centre est dans la liste des composantes de la fiche
        if ($subject->isHorsDiplome()) {
            if ($subject->getresponsableFicheMatiere()?->getId() === $this->user->getId()) {
                //si c'est l'auteur de la fiche il peut
                $access = true;
            } else {
                if ($this->user->getComposanteResponsableDpe()->count() > 0) {
                    $access = true; //si c'est un DPE alors il peut.
                }
            }

            $canEdit = ($this->ficheWorkflow->can($subject, 'valider_fiche_compo') || $this->ficheWorkflow->can($subject, 'rouvrir_fiche_matiere')) && $access;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $canEdit =
                $this->ficheWorkflow->can($subject, 'valider_fiche_compo') ||
                $this->ficheWorkflow->can($subject, 'valider_fiche_ses');
        }

        return $canEdit;
    }

    private function canManageDpeParcours(DpeParcours $subject, mixed $centre)
    {
        $parcours = $subject->getParcours();
        if ($parcours === null) {
            return false;
        }
        $canEdit = false;
        if ($parcours->getRespParcours() === $this->user || $parcours->getCoResponsable() === $this->user) {
            $canEdit = $this->dpeParcoursWorkflow->can($subject, 'valider_dpe_composante') || $this->dpeParcoursWorkflow->can($subject, 'valider_conseil')|| $this->dpeParcoursWorkflow->can($subject, 'valider_central');
        }

        if ($parcours->getFormation()?->getResponsableMention() === $this->user || $parcours->getFormation()?->getCoResponsable() === $this->user) {
            $canEdit =
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf') || $this->dpeParcoursWorkflow->can($subject, 'valider_central');
        }

        if (
            $parcours->getFormation()?->getComposantePorteuse() === $centre->getComposante() &&
            ($centre->getComposante()?->getResponsableDpe() === $this->user || $parcours->getFormation()?->getComposantePorteuse() === $centre->getComposante())) {
            $canEdit =
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf') || $this->dpeParcoursWorkflow->can($subject, 'valider_central');
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $canEdit =
                $this->dpeParcoursWorkflow->can($subject, 'autoriser') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_ouverture_sans_cfvu') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_parcours') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_rf') ||
                $this->dpeParcoursWorkflow->can($subject, 'valider_central');
        }


        return $canEdit;
    }
}
