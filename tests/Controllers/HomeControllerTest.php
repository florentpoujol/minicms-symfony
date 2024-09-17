<?php declare(strict_types=1);

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class HomeControllerTest extends WebTestCase
{
    public function testWeCanSeeTheHomePage(): void
    {
        self::createClient()->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('h1', 'Hello HomeController');
    }
}
