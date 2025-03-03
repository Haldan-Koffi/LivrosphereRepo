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

        $this->assertCount(0, $utilisateur->getLivres());

        $livre = new Livre();

        $utilisateur->addLivre($livre);
        $this->assertCount(1, $utilisateur->getLivres());
        $this->assertSame($utilisateur, $livre->getUtilisateur());
        
        $utilisateur->removeLivre($livre);
        $this->assertCount(0, $utilisateur->getLivres());
    }

}


