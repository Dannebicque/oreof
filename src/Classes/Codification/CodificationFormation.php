<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/Codification/CodificationFormation.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/12/2023 17:51
 */

namespace App\Classes\Codification;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
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

    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function setCodificationFormation(Formation $formation): void
    {
        $this->setCodificationParcours($formation);
        foreach ($formation->getParcours() as $parcours) {
            $this->setCodificationDiplome($parcours);
            $this->setCodeEtape($parcours);
            $this->setCodificationVersionEtape($parcours);
            $this->setCodificationSemestre($parcours);
            $this->entityManager->flush();
        }
    }

    public function setCodificationParcours(Formation $formation): void
    {
        $cleParcours = [];
        if ($formation->isHasParcours() === true) {
            $parcours = $formation->getParcours();
            $i = 0;
            foreach ($parcours as $p) {
                if ($p->getTypeParcours() !== TypeParcoursEnum::TYPE_PARCOURS_LAS1 && $p->getTypeParcours() !== TypeParcoursEnum::TYPE_PARCOURS_LAS23) {
                    $p->setCodeApogee(self::TAB_CODE_PARCOURS[$i]);
                    $cleParcours[trim($p->getLibelle())] = self::TAB_CODE_PARCOURS[$i];
                    $i++;
                }
            }

            foreach ($parcours as $p) {
                if (
                    $p->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_LAS1 ||
                    $p->getTypeParcours() === TypeParcoursEnum::TYPE_PARCOURS_LAS23 ||
                str_contains($p->getLibelle(), '(Alternance)')
                ) {
                    if (str_contains($p->getLibelle(), '(Alternance)')) {
                        $libelle = trim(str_replace(' (Alternance)', '', $p->getLibelle()));
                    } else {
                        $libelle = trim($p->getLibelle());
                    }
                    //un parcours LAS ne génère pas un nouveau code de parcours, on essaye de retrouver le code du parcours
                    if (array_key_exists($libelle, $cleParcours)) {
                        $p->setCodeApogee($cleParcours[$libelle ]);
                    }
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
            $code = $formation->getAnneeUniversitaire()?->getCodeApogee();
            $code .= $formation->getTypeDiplome()?->getCodeApogee();
            $code .= $formation->getDomaine()?->getCodeApogee();
            $code .= $formation->getMention()?->getCodeApogee();

            foreach ($parcours->getSemestreParcours() as $sp) {
                if ($parcours->isParcoursDefaut()) {
                    $codeParcours = '0';
                } elseif ($sp->getSemestre()?->isTroncCommun() === true) {
                    //todo: vérifier le début de semestre dans la formation
                    $codeParcours = 'X';
                } else {
                    $codeParcours = $parcours->getCodeApogee();
                }
                $sp->setCodeApogeeDiplome($code.$codeParcours); //todo: attention dépend du type d'anée
            }

            $this->setCodificationVersionDiplome($parcours);
            //$this->entityManager->flush();
        }
    }

    public function setCodificationVersionDiplome(Parcours $parcours): void
    {
        $formation = $parcours->getFormation();
        if ($formation !== null) {

            foreach ($parcours->getSemestreParcours() as $sp) {
                $code = $sp->getAnnee() === $parcours->getFormation()?->getTypeDiplome()?->getNbAnnee() ? '2' : '1';
                if ($parcours->isParcoursDefaut()) {
                    $code .= substr($formation->getComposantePorteuse()?->getCodeComposante(), 1, 2);
                } else {
                    $code .= substr($parcours->getComposanteInscription()?->getCodeComposante(), 1, 2);
                }

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
        //$this->entityManager->flush();
    }

    public function setCodificationSemestre(Parcours $parcours): void
    {
        $semestres = $parcours->getSemestreParcours();
        $formation = $parcours->getFormation();
        if ($formation !== null) {
            foreach ($semestres as $semestre) {
                if ($semestre->getSemestre() !== null && $semestre->getSemestre()->isNonDispense() === false) {
                    /*
                     * 1. Code de la composante porteuse du semestre (champs codeApogee dans Composante)
                     * 2. Code du type de diplôme (champs codeApogee dans TypeDiplome)
                     * 3. Code de la Mention (champs codeApogee dans Mention)
                     * 4. Code du Parcours (si pas de parcours X, si Tronc commun en première année X, sinon code du parcours)
                     * 5. Numéro d'ordre du semestre LMD
                     */
                    $code = substr($formation->getComposantePorteuse()?->getCodeApogee(), 0, 1);
                    $code .= $formation->getTypeDiplome()?->getCodeApogee();
                    $code .= $formation->getMention()?->getCodeApogee();

                    // si semestre de tronc commun
                    if ($parcours->isParcoursDefaut()) {
                        $code .= '0';
                    } elseif ($semestre->getSemestre()->isTroncCommun() === true) {
                        //todo: vérifier le début de semestre dans la formation
                        $code .= 'X';
                    } else {
                        $code .= $parcours->getCodeApogee();
                    }
                    $code .= $semestre->getOrdre();
                    $semestre->getSemestre()->setCodeApogee($code);

                    $this->setCodificationUe($semestre->getSemestre());
                }
            }
            //$this->entityManager->flush();
        }
    }

    private function setCodificationUe(Semestre $semestre)
    {
        $ues = $semestre->getUes();
        foreach ($ues as $ue) {
            $ue->setCodeApogee($semestre->getCodeApogee() . $ue->getOrdre());
            $this->setCodificationEc($ue);
        }
    }

    private function setCodificationEc(\App\Entity\Ue $ue)
    {
        $ecs = $ue->getElementConstitutifs();
        foreach ($ecs as $ec) {
            //todo: gérer ordre des EC de BUT avec un ordre > 99 + codif SAE (50) + stage (60), ...
            $ec->setCodeApogee($ue->getCodeApogee() . substr($ec->getOrdre(),0,2));
        }
    }
}
