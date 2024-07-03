<?php declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\Support\AcceptanceTester;
use Codeception\Test\Unit;

final class HomeTest extends Unit
{
    protected AcceptanceTester $tester;

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
