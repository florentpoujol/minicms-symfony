<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\FunctionalTester;

final class HomeTest extends \Codeception\Test\Unit
{
    protected FunctionalTester $tester;

    protected function _before(): void
    {
    }

    // tests
    public function testWeCanSeeTheHomePage(): void
    {
        $this->tester->amOnPage('home');
        $this->tester->see('Hello HomeController');
    }
}
