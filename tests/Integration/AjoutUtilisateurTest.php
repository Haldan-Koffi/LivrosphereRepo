<?php

namespace App\Tests;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AjoutUtilisateurTest extends KernelTestCase
{
    public function testInscription(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $utilisateur = new Utilisateur();
        $utilisateur->setEmail('jn@test.com');
        $utilisateur->setMotDePasse('Jea1234!@#$');
        $utilisateur->setPseudonyme('jn');

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        //Récupération de l'utilisateur via le repository
        $utilisateurRepository = $entityManager->getRepository(Utilisateur::class);
        $utilisateurTrouve = $utilisateurRepository->findOneBy(['email' => 'jn@test.com']);
        $this->assertNotNull($utilisateurTrouve);
        $this->assertEquals('jn', $utilisateurTrouve->getPseudonyme());

    
        $entityManager->remove($utilisateurTrouve);
        $entityManager->flush();
    }
}
