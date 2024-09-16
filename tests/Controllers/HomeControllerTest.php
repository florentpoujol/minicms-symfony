<?php declare(strict_types=1);

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testWeCanSeeTheHomePage(): void
    {
        $client = self::createClient();

        $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('h1', 'Hello HomeController');
    }
}
