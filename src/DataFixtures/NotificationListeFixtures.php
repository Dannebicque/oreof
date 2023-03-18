<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DataFixtures/NotificationListeFixtures.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/01/2023 21:04
 */

namespace App\DataFixtures;

use App\Entity\NotificationListe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NotificationListeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $n = new NotificationListe();
        $n->setLibelle('Ouverture d\'une campagne annuelle de collecte');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Renseigner/associer les responsables DPE composantes');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Renseigner/associer les responsables de mentions (après validation en CFVU)');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Associer les rédacteurs');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Soumission une fiche EC/saisie d\'un argumentaire sur EC');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Validation une fiche EC soumise par rédacteur');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Saisie d\'une réserve sur fiche EC/resp de formation');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Soumission d\'un projet de DPE/saisie d\'un argumentaire');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Réception réserves resp DPE');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Visa projet DPE');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Soumission projet DPE au conseil');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Saisie avis/réserves conseil');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Validation projet DPE');
        $n->setCodeNotification('');
        $n->setIsCentral(false);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Soumission projet DPE au Central');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Saisie réserves central (modifications mineures ou suites CFVU)');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Visa direct projet DPE (modifications mineures ou suites CFVU)');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Transmission VP (modifications réglementées) avec commentaire facultatif');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(false);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Saisie avis/réserves central');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Visa central projet DPE');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Soumission projet DPE à la CFVU / date de CFVU');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Saisie avis/réserves CFVU');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);

        $n = new NotificationListe();
        $n->setLibelle('Visa publication DPE / date de publication');
        $n->setCodeNotification('');
        $n->setIsCentral(true);
        $n->setIsComposante(true);
        $manager->persist($n);



        $manager->flush();
    }
}
