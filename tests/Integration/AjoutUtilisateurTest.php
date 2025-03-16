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
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        
        // Récupération du service de hachage de mot de passe
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $utilisateur = new Utilisateur();
        $utilisateur->setEmail('jn@test.com');
        $utilisateur->setPseudonyme('jn');

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, 'Jea1234!@#$');
        $utilisateur->setMotDePasse($hashedPassword);

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        // Récupération de l'utilisateur via le repository
        $utilisateurRepository = $entityManager->getRepository(Utilisateur::class);
        $utilisateurTrouve = $utilisateurRepository->findOneBy(['email' => 'jn@test.com']);
        
        $this->assertNotNull($utilisateurTrouve);
        $this->assertEquals('jn', $utilisateurTrouve->getPseudonyme());

        // Vérification que le mot de passe stocké est bien haché
        $this->assertNotEquals('Jea1234!@#$', $utilisateurTrouve->getMotDePasse());
        $this->assertTrue(password_verify('Jea1234!@#$', $utilisateurTrouve->getMotDePasse()));

        // Suppression de l'utilisateur après le test
        $entityManager->remove($utilisateurTrouve);
        $entityManager->flush();
    }
}
