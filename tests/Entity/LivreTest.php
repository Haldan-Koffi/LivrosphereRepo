<?php

namespace App\Tests;

use App\Entity\Livre;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class LivreTest extends TestCase
{
    public function testLivresCollection(): void
    {
        $utilisateur = new Utilisateur();

        // La collection doit être initialement vide.
        $this->assertCount(0, $utilisateur->getLivres());

        $livre = new Livre();

        // Ajout du livre par l'utilisateur et vérification de la relation
        $utilisateur->addLivre($livre);
        $this->assertCount(1, $utilisateur->getLivres());
        $this->assertSame($utilisateur, $livre->getUtilisateur());
        
        $utilisateur->removeLivre($livre);
        $this->assertCount(0, $utilisateur->getLivres());
    }

}
