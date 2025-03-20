<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilControllerTest extends WebTestCase
{
    public function testHomeButton(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.bouton-inscriptionc a.btn.btn-dark', "S'inscrire");
        $this->assertSelectorExists('.bouton-inscriptionc a.btn.btn-secondary', "Se connecter");
    }
}
