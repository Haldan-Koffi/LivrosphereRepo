<?php

namespace App\Tests;

use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    public function testgetNom(): void
    {
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Ben');
        $response_nom = $utilisateur->getNom();
        $this->assertIsString($response_nom);
    }

    public function testgetEmail(): void 
    {
        $utilisateur2 = new Utilisateur();
        $utilisateur2->setEmail('haldan.user@test.com');
        $email_utilisateur = $utilisateur2->getEmail();

        $this->assertStringContainsString('@', $email_utilisateur);
    }
}
