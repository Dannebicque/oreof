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
        }

    }

    public function setCodificationParcours(Formation $formation): void
    {
        if ($formation->isHasParcours() === true) {
            $parcours = $formation->getParcours();
            $i = 0;
            foreach ($parcours as $p) {
                $p->setCodeApogee(self::TAB_CODE_PARCOURS[$i]);
                $i++;
            }

            $this->entityManager->flush();
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
            $code .= $parcours->getCodeApogee();
            $parcours->setCodeApogeeDiplome($code);
            $this->entityManager->flush();
        }
    }

    public function setCodificationVersionEtape(Parcours $parcours): void
    {
        /*
         * 1. Régime d'inscription si FI/FC... identiques, 2 si FC différente, 3 si Alternance avec structure différente, 6 pour les LAS
         * 2. code du site de formation (champs codeApogee dans Ville)
         * 3. version d'accréditation (dépend de la version de Formation)
         */
        $formation = $parcours->getFormation();
        if ($formation !== null) {
            $code = $parcours->getCodeRegimeInscription();
            $code .= $parcours->getLocalisation()?->getCodeApogee();
            $code .= substr($formation->getVersion(), 0, 1);
            $parcours->setCodeApogeeVersion($code);
            $this->entityManager->flush();
        }
    }

    public function setCodeEtape(Parcours $parcours): void
    {
        $semestres = $parcours->getSemestreParcours();

        foreach ($semestres as $semestre) {
            $semestre->setCodeApogeeEtapeAnnee($parcours->getCodeApogeeDiplome() . $semestre->getOrdreAnnee());
        }
        $this->entityManager->flush();
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
                    $code = $formation->getComposantePorteuse()?->getCodeApogee();
                    $code .= $formation->getTypeDiplome()?->getCodeApogee();
                    $code .= $formation->getMention()?->getCodeApogee();

                    // si semestre de tronc commun
                    if ($semestre->getSemestre()->isTroncCommun() === true) {
                        //todo: vérifier le début de semestre dans la formation
                        $code .= 'X';
                    } else {
                        $code .= $parcours->getCodeApogee();
                    }
                    $code .= $semestre->getOrdre();
                    $semestre->getSemestre()->setCodeApogee($code);
                }
            }
            $this->entityManager->flush();
        }
    }
}
