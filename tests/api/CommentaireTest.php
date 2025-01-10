<?php

namespace App\Tests;

use App\Entity\Commentaire;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class CommentaireTest extends ApiTestCase
{
    public function testSomething(): void
    {
        $response = static::createClient()->request('GET', 'http://localhost:8000/api/commentaires');

        $this->assertResponseIsSuccessful();
        // $this->assertJsonContains(['@id' => '/']);
    }
}
