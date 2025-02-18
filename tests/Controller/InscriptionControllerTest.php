<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InscriptionControllerTest extends WebTestCase
{
    public function testUserRegistration(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/inscription');

        
        $this->assertSelectorExists('form');

        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');
        $this->assertNotEmpty($csrfToken);

        $form = $crawler->selectButton('M\'inscrire')->form([
            'nom'           => 'Jo',
            'prenom'        => 'D',
            'email'         => 'jod@example.com',
            'mot_de_passe'  => 'AncD1234!@#$',
            'pseudonyme'    => 'jod',
            '_csrf_token'   => $csrfToken,
        ]);

        $client->submit($form);

        // Générer l'URL attendue via le routeur
        $expectedUrl = $client->getContainer()->get('router')->generate('app_accueil');
        
        // Vérifier que la réponse redirige bien vers l'URL attendue
        $this->assertResponseRedirects($expectedUrl);
    }
}
