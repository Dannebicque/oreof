<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DataFixtures/UsersFixtures.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    //constructeur avec password
    public const RESPONSABLE_DPE_COMPOSANTE = 'resp_dpe';
    public const RESPONSABLE_FORMATION = 'resp_formation';

    public function __construct(private readonly UserPasswordHasherInterface $encoder)
    {
    }


    public function load(ObjectManager $manager): void
    {
        //admin
        $userAdmin = new User();
        $userAdmin->setEmail('admin@mail.com');
        $userAdmin->setUsername('admin');
        $userAdmin->setNom('Admin');
        $userAdmin->setPrenom('John');
        $userAdmin->setRoles(['ROLE_ADMIN']);
        $password = $this->encoder->hashPassword($userAdmin, 'test');
        $userAdmin->setPassword($password);
        $manager->persist($userAdmin);

        //SES
        $userSes = new User();
        $userSes->setEmail('ses@mail.com');
        $userSes->setUsername('ses');
        $userSes->setNom('Scolarité');
        $userSes->setPrenom('John');
        $userSes->setRoles(['ROLE_SES']);
        $password = $this->encoder->hashPassword($userSes, 'test');
        $userSes->setPassword($password);
        $manager->persist($userSes);

        //VP
        $userVp = new User();
        $userVp->setEmail('vp@mail.com');
        $userVp->setUsername('vp');
        $userVp->setNom('Vice');
        $userVp->setPrenom('John');
        $userVp->setRoles(['ROLE_VP']);
        $password = $this->encoder->hashPassword($userVp, 'test');
        $userVp->setPassword($password);
        $manager->persist($userVp);

        //Responsable DPE Composante
        $userDpe = new User();
        $userDpe->setEmail('resp-dpe@mail.com');
        $userDpe->setUsername('resp-dpe');
        $userDpe->setNom('Responsable');
        $userDpe->setPrenom('John');
        $userDpe->setRoles(['ROLE_RESP_DPE']);
        $password = $this->encoder->hashPassword($userDpe, 'test');
        $userDpe->setPassword($password);

        $manager->persist($userDpe);


        //Responsable Formation
        $userRespFormation = new User();
        $userRespFormation->setEmail('formation@mail.com');
        $userRespFormation->setUsername('formation');
        $userRespFormation->setNom('Formation');
        $userRespFormation->setPrenom('John');
        $userRespFormation->setRoles(['ROLE_RESP_FORMATION']);
        $password = $this->encoder->hashPassword($userRespFormation, 'test');
        $userRespFormation->setPassword($password);
        $this->addReference(self::RESPONSABLE_FORMATION, $userDpe);

        $manager->persist($userRespFormation);

        //Responsable EC
        $userRespFormation = new User();
        $userRespFormation->setEmail('resp-ec@mail.com');
        $userRespFormation->setUsername('resp-ec');
        $userRespFormation->setNom('Element');
        $userRespFormation->setPrenom('John');
        $userRespFormation->setRoles(['ROLE_RESP_EC']);
        $password = $this->encoder->hashPassword($userRespFormation, 'test');
        $userRespFormation->setPassword($password);
        $manager->persist($userRespFormation);

        $manager->flush();

        $this->addReference(self::RESPONSABLE_DPE_COMPOSANTE, $userDpe);
    }
}
