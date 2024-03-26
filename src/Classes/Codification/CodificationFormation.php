<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Codification/CodificationFormation.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/12/2023 17:51
 */

namespace App\Classes\Codification;

use App\Classes\CalculStructureParcours;
use App\DTO\StructureEc;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Enums\TypeParcoursEnum;
use Doctrine\ORM\EntityManagerInterface;

class CodificationFormation
{
    // définir un tableau de constante avec les chiffres puis les 26 lettres de l'alphabet
    public const TAB_CODE_PARCOURS = [
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '9',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'Y',
        'Z',
    ];

    private ?Parcours $parcours = null;
    private int $ordreEc;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected CalculStructureParcours $calculStructreParcours
    ) {
    }

    public function setCodificationFormation(Formation $formation): void
    {
        //todo: calcul structure ??
        $this->setCodificationParcours($formation);
        foreach ($formation->getParcours() as $parcours) {
            //todo: tester si codif haute est figée pour ne pas recalculer
            $this->setCodificationDiplome($parcours);
            $this->setCodeEtape($parcours);
            $this->setCodificationVersionEtape($parcours);

            //recalculer les semestres même si déjà codifiés selon la partie haute
            $this->setCodificationSemestre($parcours);
            $this->entityManager->flush();
        }
    }

    public function setCodificationParcours(Formation $formation): void
    {
        if ($formation->isHasParcours() === true) {
            $parcours = $formation->getParcours();
            $i = 0;
            foreach ($parcours as $p) {
                if ($p->getParcoursOrigine() === null) {
                    $p->setCodeApogee(self::TAB_CODE_PARCOURS[$i]);
                    $i++;
                }
            }

            foreach ($parcours as $p) {
                if ($p->getParcoursOrigine() !== null) {
                    $p->setCodeApogee($p->getParcoursOrigine()->getCodeApogee());
                }
            }
        }
    }

    public function setCodificationDiplome(Parcours $parcours): void
    {
        /*
         * 1. code accréditation : 6 pour l'accréditation 2024, se trouve dans AnneeUniversitaire
         * 2. code LMD du TypeDiplome (champs codeApogee)
         * 3. code du Domaine (champs codeApogee)
         * 4. Identification de la Mention (champs codeApogee)
         * 5. Identification du Parcours (champs codeApogee)
         */
        $formation = $parcours->getFormation();
        if ($formation !== null) {
            $code = $formation->getDpe()?->getCodeApogee();
            $code .= $formation->getTypeDiplome()?->getCodeApogee();
            $code .= $formation->getDomaine()?->getCodeApogee();
            $code .= $parcours->getCodeMentionApogee();

            foreach ($parcours->getSemestreParcours() as $sp) {
                if ($parcours->isParcoursDefaut()) {
                    $codeParcours = '0';
                } elseif ($formation->getParcours()->count() === 1) {
                    $codeParcours = '1';
                } elseif ($sp->getSemestre()?->isTroncCommun() === true && $formation->getParcours()->count() > 1) {
                    //Si tronc commun et plus de 1 parcours
                    $codeParcours = 'X';
                } else {
                    $codeParcours = $parcours->getCodeApogee();
                }
                $sp->setCodeApogeeDiplome($code.$codeParcours); //todo: attention dépend du type d'anée
            }

            $this->setCodificationVersionDiplome($parcours);
        }
    }

    public function setCodificationVersionDiplome(Parcours $parcours): void
    {
        $formation = $parcours->getFormation();
        if ($formation !== null) {
            foreach ($parcours->getSemestreParcours() as $sp) {
                if ($parcours->getFormation()?->getTypeDiplome()?->isCodifIntermediaire() === false) {
                    $code = '2';
                } else {
                    if ($parcours->getTypeParcours() !== null && ($parcours->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_LAS1 || $parcours->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_LAS23)) {
                        $code = $sp->getAnnee() === $parcours->getFormation()?->getTypeDiplome()?->getNbAnnee() ? '6' : '1';
                    } else {

                        $code = $sp->getAnnee() === $parcours->getFormation()?->getTypeDiplome()?->getNbAnnee() ? '2' : '1';
                    }
                }

                $code .= substr($parcours->getComposanteInscription()?->getCodeComposante(), 1, 2);
                $sp->setCodeApogeeVersionDiplome($code);
            }
        }
    }

    public function setCodificationVersionEtape(Parcours $parcours): ?string
    {
        /*
         * 1. Régime d'inscription si FI/FC... identiques, 2 si FC différente, 3 si Alternance avec structure différente, 6 pour les LAS
         * 2. code du site de formation (champs codeApogee dans Ville)
         * 3. version d'accréditation (dépend de la version de Formation)
         */
        $formation = $parcours->getFormation();
        if ($formation !== null) {
            if ($parcours->getTypeParcours() !== null && ($parcours->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_LAS1 || $parcours->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_LAS23)) {
                $code = '6';
            } else {
                $code = $parcours->getCodeRegimeInscription();
            }

            if ($parcours->isParcoursDefaut()) {
                $code .= $parcours->getFormation()?->getLocalisationMention()->first()?->getCodeApogee();
            } else {
                $code .= $parcours->getLocalisation()?->getCodeApogee();
            }


            $code .= $parcours->getCodeApogeeNumeroVersion();
            return $code;
        }

        return null;
    }

    public function setCodeEtape(Parcours $parcours): void
    {
        $semestres = $parcours->getSemestreParcours();

        foreach ($semestres as $semestre) {
            $semestre->setCodeApogeeEtapeAnnee($parcours->getCodeDiplome($semestre->getAnnee()) . $semestre->getAnnee());
            $semestre->setCodeApogeeEtapeVersion($this->setCodificationVersionEtape($parcours));
        }
    }

    public function setCodificationSemestre(Parcours $parcours): void
    {
        // structure semestre
        $structureParcours = $this->calculStructreParcours->calcul($parcours, false, false);


       $semestres = $structureParcours->semestres;
        $formation = $parcours->getFormation();
        if ($formation !== null) {
            foreach ($semestres as $semestre) {
                if ($semestre->semestre !== null && $semestre->semestre->isNonDispense() === false) {
                    /*
                     * 1. Code de la composante d'inscription du semestre (champs codeApogee dans Composante)
                     * 2. Code du type de diplôme (champs codeApogee dans TypeDiplome)
                     * 3. Code de la Mention (champs codeApogee dans Mention)
                     * 4. Code du Parcours (si pas de parcours X, si Tronc commun en première année X, sinon code du parcours)
                     * 5. Numéro d'ordre du semestre LMD
                     */
                    $code = substr($parcours->getComposanteInscription()?->getCodeApogee(), 0, 1);
                    $code .= $formation->getTypeDiplome()?->getCodeApogee();
                    $code .= substr($semestre->semestreParcours->getCodeApogeeDiplome(), 3, 2); //3 et 4 = 4 et 5 de diplôme
                    $code .= $semestre->ordre;
                    $semestre->semestre->setCodeApogee($code);

                    $this->setCodificationUe($semestre);
                }
            }
        }
    }

    private function setCodificationUe(StructureSemestre $semestre): void
    {
        $ues = $semestre->ues();
        foreach ($ues as $ue) {
            $this->ordreEc = 1;
            $ordreUe = 1;
            if ($ue->ue->getUeParent() === null) { //UE parent
               if (count($ue->uesEnfants()) > 0) {
                   //UE avec UE enfants
                   $ue->ue->setCodeApogee($semestre->semestre->getCodeApogee() . $ue->ordre().'X'); //UE à choix on met un X
                   foreach ($ue->uesEnfants() as $ueEnfant) {
                       if ($ueEnfant->ue->getNatureUeEc()?->isLibre() === true) {
                           $ueEnfant->ue->setCodeApogee($semestre->semestre->getCodeApogee() . $ue->ordre(). chr(64+$ordreUe).'X');
                           $ordreUe++;
                       } else {
                           $ueEnfant->ue->setCodeApogee($semestre->semestre->getCodeApogee() . $ue->ordre(). chr(64+$ordreUe));
                           $this->setCodificationEc($ueEnfant, true);
                            $ordreUe++;
                       }
                   }
               } else {
                   $ue->ue->setCodeApogee($semestre->semestre->getCodeApogee() . $ue->ordre());
                   //UE sans UE enfants
                   $this->setCodificationEc($ue, false);
               }
            }
        }
    }

    private function setCodificationEc(StructureUe $ue, bool $isUeEnfant = false): void
    {
        $ecs = $ue->elementConstitutifs;
        foreach ($ecs as $ec) {
            if ($ec->elementConstitutif->getEcParent() === null) {
                if (count($ec->elementsConstitutifsEnfants) > 0) {
                    //code de l'EC parent
                    $ec->elementConstitutif->setCodeApogee($ue->ue->getCodeApogee() . $ec->elementConstitutif->getOrdre());
                    //EC avec EC enfants
                    foreach ($ec->elementsConstitutifsEnfants as $ecEnfant) {
                        $this->setCodificationFicheMatiere($ecEnfant, $ue, $isUeEnfant, true);
                    }
                } else {
                    //EC sans EC enfants
                    $this->setCodificationFicheMatiere($ec, $ue, $isUeEnfant, false);
                }
            }
        }
    }

    private function setCodificationFicheMatiere(StructureEc $ec, StructureUe $ue, bool $isUeEnfant, bool $isEcEnfant)
    {
        if ($ec->elementConstitutif->getFicheMatiere() !== null) {
            if ($ec->elementConstitutif->getFicheMatiere()->getParcours() === $this->parcours) {
                $code = $ue->ue->getCodeApogee();
                    if ($isEcEnfant === true) {
                        $code .= $ec->elementConstitutif->getEcParent()?->getOrdre();
                        $code .= chr(64 + $this->ordreEc);
                        $this->ordreEc++;
                    } else {
                        $code .= $ec->elementConstitutif->getOrdre();
                    }
                $ec->elementConstitutif->getFicheMatiere()->setCodeApogee($code);
            } else {
                if ($isEcEnfant === true) {
                    $this->ordreEc++; //EC enfant, on incrément l'ordre malgré tout pour garder la cohérence des lettres
                }
            }

            if ($ec->elementConstitutif->getParcours() !== null && $ec->elementConstitutif->getParcours()->getFormation() !== $ec->elementConstitutif->getFicheMatiere()->getParcours()?->getFormation()) {
                $ec->elementConstitutif->getFicheMatiere()->setTypeApogee(FicheMatiere::MATM);
            }
        } else {
            $code = $ue->ue->getCodeApogee();
            if ($isUeEnfant === true) {
                $code .= chr(64 + $this->ordreEc);
                $this->ordreEc++;
            } else {
                if ($ec->elementConstitutif->getNatureUeEc()?->isLibre() && $isEcEnfant === true) {
                    $code .= $ec->elementConstitutif->getEcParent()?->getOrdre();
                    $code .= chr(64 + $this->ordreEc);
                    $this->ordreEc++;
                } else {
                    $code .= $ec->elementConstitutif->getOrdre();
                }
            }
            $ec->elementConstitutif->setCodeApogee($code);
        }
    }

    public function setCodificationHaute(Formation $formation)
    {
        $this->setCodificationParcours($formation);
        foreach ($formation->getParcours() as $parcours) {
            //todo: tester si codif haute est figée pour ne pas recalculer
            $this->setCodificationDiplome($parcours);
            $this->setCodeEtape($parcours);
            $this->setCodificationVersionEtape($parcours);
            $this->entityManager->flush();
        }
    }

    public function setCodificationBasse(Formation $formation): void
    {
        //$this->setCodificationParcours($formation);
        foreach ($formation->getParcours() as $parcours) {
            $this->parcours = $parcours;
            //recalculer les semestres même si déjà codifiés selon la partie haute
            $this->setCodificationSemestre($parcours);
            $this->entityManager->flush();
        }
    }
}
