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
            'nom'           => 'Test',
            'prenom'        => 'us',
            'email'         => 'azer@example.com',
            'mot_de_passe'  => 'UsdD1234!@#$',
            'pseudonyme'    => 'ud',
            '_csrf_token'   => $csrfToken,
        ]);
#(UseD1234!@#$)
#'HalD1234!@#$'
        $client->submit($form);

        // Générer l'URL attendue via le routeur
        $expectedUrl = $client->getContainer()->get('router')->generate('app_accueil');
        
        // Vérifier que la réponse redirige bien vers l'URL attendue
        $this->assertResponseRedirects($expectedUrl);
    }
}
