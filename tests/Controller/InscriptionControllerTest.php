<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InscriptionControllerTest extends WebTestCase
{
    public function testUserRegistration(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/inscription');

        // Vérifier que le formulaire est bien présent en utilisant un sélecteur plus générique
        $this->assertSelectorExists('form'); // Vérifie simplement la présence du formulaire

        // Récupérer le jeton CSRF
        $csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');
        
        // Vérifier que le jeton est bien récupéré
        $this->assertNotEmpty($csrfToken);

        // Soumettre le formulaire avec les données nécessaires
        $form = $crawler->selectButton('M\'inscrire')->form([
            'nom' => 'John',
            'prenom' => 'Doe',
            'email' => 'john.doe@example.com',
            'mot_de_passe' => 'AbcD1234!@#$',
            'pseudonyme' => 'johndoe',
            '_csrf_token' => $csrfToken, // Utiliser le bon nom pour le CSRF
        ]);

        $client->submit($form);

        // Vérifier si l'utilisateur est redirigé
        $this->assertResponseRedirects('/accueil');
    }

}
