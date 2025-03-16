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

        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $form = $crawler->selectButton('M\'inscrire')->form([
            'email'            => 'nw@example.com',
            'mot_de_passe'     => 'UsdD1234!@#$',
            'pseudonyme'       => 'nw',
            'date_inscription' => $now,
            '_csrf_token'      => $csrfToken,
        ]);

        $client->submit($form);

        $expectedUrl = $client->getContainer()->get('router')->generate('app_accueil');
        
        $this->assertResponseRedirects($expectedUrl);
    }

}




#(UseD1234!@#$)
#'HalD1234!@#$'
