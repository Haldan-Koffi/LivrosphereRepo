<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.bouton-inscriptionc a.btn.btn-dark', "S'inscrire");
        $this->assertSelectorExists('.bouton-inscriptionc a.btn.btn-dark', "Se connecter");
    }
}
