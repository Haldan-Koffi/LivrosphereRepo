<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UtilisateurControllerTest extends WebTestCase
{

    public function testLabelPseudonyme(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/inscription');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('label', 'Pseudonyme');
        $this->assertSelectorExists('form.bloc-inscription button.btn-dark', "M'inscrire");
    }
}