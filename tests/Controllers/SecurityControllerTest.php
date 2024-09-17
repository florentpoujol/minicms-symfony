<?php declare(strict_types=1);

namespace App\Tests\Controllers ;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class SecurityControllerTest extends WebTestCase
{
    private readonly KernelBrowser $client;

    private readonly UserRepository $userRepository;
    private readonly User $user;
    private readonly User $writer;
    private readonly User $admin;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager(); // @phpstan-ignore-line

        $this->userRepository = $entityManager->getRepository(User::class);

        $this->user = $this->userRepository->findOneBy(['email' => 'user@example.com']);
        $this->writer = $this->userRepository->findOneBy(['email' => 'writer@example.com']);
        $this->admin = $this->userRepository->findOneBy(['email' => 'admin@example.com']);
    }

    public function testAnonCantAccessProfile(): void
    {
        $this->client->request(Request::METHOD_GET, '/profile');

        self::assertResponseRedirects('/login');
    }

    public function testAnonCantAccessAdmin(): void
    {
        $this->client->request(Request::METHOD_GET, '/admin');

        self::assertResponseRedirects('/login');
    }

   // public function testUserCantAccessAdmin(): void
   // {
   //     // TODO Seed user
   //     $this->tester->amLoggedInAs();
   //     $this->tester->amOnPage('/admin');
   //     $this->tester->dontSeeAuthentication();
   // }

    // public function testDontSeeRememberedAuthentication(): void
    // {
    //     $this->tester->amOnPage('/login');
    //     $this->tester->submitForm('form[name=login]', [
    //         'email' => 'john_doe@gmail.com',
    //         'password' => '123456',
    //         '_remember_me' => false,
    //     ]);
    //     $this->tester->dontSeeRememberedAuthentication();
    // }

    /**
     * @return list<list<string>>
     */
    public function getUsers(): array
    {
        return [
            ['user'],
            ['writer'],
            ['admin'],
        ];
    }

    /**
     * @dataProvider getUsers
     */
    public function testUserHasRole(string $propertyName): void
    {
        $user = $this->{$propertyName}; // @phpstan-ignore-line (variable property access)
        \assert($user instanceof User);

        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/');

        $roles = $user->getRoles();

        self::assertTrue(\in_array('ROLE_USER', $roles, true));

        if ($user->getEmail() === 'writer@example.com') { // writer and admin
            self::assertTrue(\in_array('ROLE_WRITER', $roles, true));
            self::assertFalse(\in_array('ROLE_ADMIN', $roles, true));
        }

        if ($user->getEmail() === 'admin@example.com') {
            self::assertFalse(\in_array('ROLE_WRITER', $roles, true));
            self::assertTrue(\in_array('ROLE_ADMIN', $roles, true));
        }
    }

    /**
     * @dataProvider getUserEmails
     */
    // public function testLoggedInUserCanSeeProfile(int $userId): void
    // {
    //     $repo = $this->tester->grabRepository(UserRepository::class);
    //     $user = $repo->find($userId);
    //
    //     $this->tester->amLoggedInAs($user);
    //     $this->tester->amOnPage('/profile');
    //
    //     $this->tester->seeAuthentication();
    // }

    /**
     * @dataProvider getUserEmails
     */
    // public function testLoggedInUserCanSeeAdmin(int $userId): void
    // {
    //     if ($userId == 1) {
    //         $this->markTestSkipped();
    //     }
    //
    //     $repo = $this->tester->grabRepository(UserRepository::class);
    //     $user = $repo->find($userId);
    //
    //     $this->tester->amLoggedInAs($user);
    //     $this->tester->amOnPage('/admin');
    //
    //     $this->tester->seeAuthentication();
    // }
}
