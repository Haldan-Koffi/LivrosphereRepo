<?php

namespace App\Tests;

use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    public function testgetPseudonyme(): void
    {
        $utilisateur = new Utilisateur();
        $utilisateur->setPseudonyme('Ben');
        $response_pseudo = $utilisateur->getPseudonyme();
        $this->assertIsString($response_pseudo);
    }

    public function testgetEmail(): void 
    {
        $utilisateur2 = new Utilisateur();
        $utilisateur2->setEmail('haldan.user@test.com');
        $email_utilisateur = $utilisateur2->getEmail();

        $this->assertStringContainsString('@', $email_utilisateur);
    }
}

