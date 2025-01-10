<?php

namespace App\Tests;

use App\Entity\Livre;
use PHPUnit\Framework\TestCase;

class LivreTest extends TestCase
{
    public function testgetAnneePublication(): void
    {
        $this->assertConntains($annee_publication);
    }
}
