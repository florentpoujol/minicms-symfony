<?php declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\FunctionalTester;
use Codeception\Test\Unit;

final class HomeTest extends Unit
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
