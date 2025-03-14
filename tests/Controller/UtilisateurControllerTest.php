<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UtilisateurControllerTest extends WebTestCase
{
    // public function testSomething(): void
    // {
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/inscription');

    //     // $this->assertResponseIsSuccessful();
    //     $this->assertSelectorTextContains('label', 'Nom');
    //     $this->assertSelectorExists('form.bloc-inscription button.btn-dark', "M'inscrire");
    // }

    public function testSome(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/inscription');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('label', 'Pseudonyme');
        $this->assertSelectorExists('form.bloc-inscription button.btn-dark', "M'inscrire");
    }
}