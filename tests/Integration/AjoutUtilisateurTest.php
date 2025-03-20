<?php

namespace App\Tests;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AjoutUtilisateurTest extends KernelTestCase
{
    public function testInscription(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $utilisateur = new Utilisateur();
        $utilisateur->setEmail('jn@test.com');
        $utilisateur->setPseudonyme('jn');

        $hashedPassword = $passwordHasher->hashPassword($utilisateur, 'Jea1234!@#$');
        $utilisateur->setMotDePasse($hashedPassword);
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $utilisateurRepository = $entityManager->getRepository(Utilisateur::class);
        $utilisateurTrouve = $utilisateurRepository->findOneBy(['email' => 'jn@test.com']);
        
        $this->assertNotNull($utilisateurTrouve);
        $this->assertEquals('jn', $utilisateurTrouve->getPseudonyme());

        $this->assertNotEquals('Jea1234!@#$', $utilisateurTrouve->getMotDePasse());
        $this->assertTrue(password_verify('Jea1234!@#$', $utilisateurTrouve->getMotDePasse()));

        $entityManager->remove($utilisateurTrouve);
        $entityManager->flush();
    }
}
