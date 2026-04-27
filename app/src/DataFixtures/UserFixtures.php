<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {

        // Création d'un user admin
        $user = new User();
        $password = $this->hasher->hashPassword($user, 'admin');

        $user->setEmail('admin@esprit-deco.fr')
        ->setPassword($password)
        ->setRoles(['ROLE_ADMIN'])
        ->setIsVerified(true)
        ;
        $manager->persist($user);

        // Création d'un user non admin
        $user = new User();
        $password = $this->hasher->hashPassword($user, 'pass');

        $user->setEmail("toto@gmail.com")
        ->setPassword($password)
        ->setRoles(['ROLE_USER'])
        ->setIsVerified(true)
        ;
        $manager->persist($user);        

        $manager->flush();
    }
}
