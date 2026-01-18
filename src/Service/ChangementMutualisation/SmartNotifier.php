<?php
/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/ChangementMutualisation/SmartNotifier.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/12/2025 12:24
 */

// src/Service/SmartNotifier.php
namespace App\Service\ChangementMutualisation;

use App\Entity\FicheMatiere;
use App\Entity\Formation;
use App\Entity\NotificationLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SmartNotifier
{
    private const FENETRE_SILENCE = 900; // 15 minutes

    public function __construct(
        private EntityManagerInterface $em,
        private ImpactNotifier         $notifier,
        private ChangesetDetector      $changesetDetector,
        private LoggerInterface        $logger
    )
    {
    }

    /**
     * Point d'entrée principal : détecte et traite les modifications
     */
    public function traiterModifications(FicheMatiere $fiche, User $auteur): array
    {
        // 1. Détecter tous les changements
        $tousLesChangements = $this->changesetDetector->detecterChangements($fiche);

        if (empty($tousLesChangements)) {
            return [
                'envoyees' => [],
                'supprimees' => [],
                'changements' => []
            ];
        }

        // 2. Filtrer uniquement les changements impactants
        $changementsImpactants = $this->changesetDetector->filtrerChangementsImpactants($tousLesChangements);

        if (empty($changementsImpactants)) {
            // Il y a des changements mais pas impactants
            $this->logger->info('Modifications non impactantes détectées', [
                'fiche_id' => $fiche->getId(),
                'champs' => array_keys($tousLesChangements)
            ]);

            return [
                'envoyees' => [],
                'supprimees' => [],
                'changements' => $tousLesChangements
            ];
        }

        // todo: 3. Récupérer les formations impactées
        $formations = $fiche->getFormations();

        if ($formations->isEmpty()) {
            $this->logger->info('Changements impactants mais aucune formation liée', [
                'fiche_id' => $fiche->getId()
            ]);

            return [
                'envoyees' => [],
                'supprimees' => [],
                'changements' => $changementsImpactants
            ];
        }

        // 4. Notifier
        $resultats = $this->notifierModificationImpactante(
            $fiche,
            $formations->toArray(),
            $changementsImpactants,
            $auteur
        );

        $resultats['changements'] = $changementsImpactants;

        return $resultats;
    }

    /**
     * Envoie les notifications en évitant les doublons
     */
    private function notifierModificationImpactante(
        FicheMatiere $fiche,
        array        $formations,
        array        $changements,
        User         $auteur
    ): array
    {
        $resultats = [
            'envoyees' => [],
            'supprimees' => []
        ];

        // Créer le hash une seule fois
        $hash = $this->changesetDetector->creerHashChangements($changements);

        foreach ($formations as $formation) {
            $responsable = $formation->getResponsable();

            if (!$responsable) {
                $this->logger->warning('Formation sans responsable', [
                    'formation_id' => $formation->getId(),
                    'formation_nom' => $formation->getNom()
                ]);
                continue;
            }

            // Vérifier si notification déjà envoyée
            if ($this->aDejaEteNotifieRecemment($fiche, $formation, $responsable, $hash)) {
                $resultats['supprimees'][] = $formation->getNom();

                $this->logger->info('Notification supprimée (doublon)', [
                    'fiche_id' => $fiche->getId(),
                    'formation_id' => $formation->getId(),
                    'responsable_email' => $responsable->getEmail(),
                    'hash' => $hash
                ]);

                continue;
            }

            // Envoyer la notification
            try {
                $this->notifier->envoyer($responsable, [
                    'type' => 'modification_fiche',
                    'fiche' => $fiche,
                    'formation' => $formation,
                    'changements' => $this->changesetDetector->formatterChangementsPourAffichage($changements),
                    'auteur' => $auteur,
                    'timestamp' => new \DateTimeImmutable()
                ]);

                // Logger la notification
                $this->enregistrerNotification($fiche, $formation, $responsable, $changements, $hash);

                $resultats['envoyees'][] = $formation->getNom();

                $this->logger->info('Notification envoyée', [
                    'fiche_id' => $fiche->getId(),
                    'formation_id' => $formation->getId(),
                    'responsable_email' => $responsable->getEmail(),
                    'champs_modifies' => array_keys($changements)
                ]);

            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de l\'envoi de notification', [
                    'fiche_id' => $fiche->getId(),
                    'formation_id' => $formation->getId(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Sauvegarder les logs
        $this->em->flush();

        return $resultats;
    }

    /**
     * Vérifie si une notification similaire a été envoyée récemment
     */
    private function aDejaEteNotifieRecemment(
        FicheMatiere $fiche,
        Formation    $formation,
        User         $responsable,
        string       $hash
    ): bool
    {
        $dateLimite = new \DateTimeImmutable('-' . self::FENETRE_SILENCE . ' seconds');

        $derniereNotif = $this->em->getRepository(NotificationLog::class)
            ->createQueryBuilder('nl')
            ->where('nl.ficheMatiere = :fiche')
            ->andWhere('nl.formation = :formation')
            ->andWhere('nl.responsable = :responsable')
            ->andWhere('nl.createdAt > :dateLimite')
            ->andWhere('nl.hash = :hash')
            ->setParameter('fiche', $fiche)
            ->setParameter('formation', $formation)
            ->setParameter('responsable', $responsable)
            ->setParameter('dateLimite', $dateLimite)
            ->setParameter('hash', $hash)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $derniereNotif !== null;
    }

    /**
     * Enregistre la notification dans les logs
     */
    private function enregistrerNotification(
        FicheMatiere $fiche,
        Formation    $formation,
        User         $responsable,
        array        $changements,
        string       $hash
    ): void
    {
        $log = new NotificationLog();
        $log->setFicheMatiere($fiche);
        $log->setFormation($formation);
        $log->setResponsable($responsable);
        $log->setChangements($changements);
        $log->setCreatedAt(new \DateTimeImmutable());
        $log->setHash($hash);

        $this->em->persist($log);
    }

    /**
     * Permet de personnaliser la durée de la fenêtre de silence
     */
    public function setFenetreSilence(int $secondes): void
    {
        // Pour des tests ou cas particuliers
        // Note: utiliser une constante de classe en prod
    }
}
