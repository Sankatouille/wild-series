<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    protected $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        $contributor = new User();
        $contributor->setEmail('contributor@wildseries.com');
        $contributor->setRoles(["ROLE_CONTRIBUTOR"]);
        $this->addReference("Contributor", $contributor);

        $hashedPassword = $this->passwordHasher->hashPassword($contributor, "1234");
        $contributor->setPassword($hashedPassword);

        $manager->persist($contributor);



        $admin = new User();
        $admin->setEmail('admin@wildseries.com');
        $admin->setRoles(["ROLE_ADMIN"]);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, "root");
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);



        $manager->flush();
    }
}
