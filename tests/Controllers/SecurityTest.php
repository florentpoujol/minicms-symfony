<?php declare(strict_types=1);

namespace App\Tests\Controllers ;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityTest extends WebTestCase
{
    private readonly UserRepository $userRepository;
    private readonly User $user;
    private readonly User $writer;
    private readonly User $admin;

    protected function setUp(): void
    {
        // @phpstan-ignore-next-line
        $this->userRepository = $this->tester->grabRepository(UserRepository::class);

        $this->user = $this->userRepository->findOneBy(['email' => 'user@example.com']);
        $this->writer = $this->userRepository->findOneBy(['email' => 'writer@example.com']);
        $this->admin = $this->userRepository->findOneBy(['email' => 'admin@example.com']);
    }

    public function testAnonCantAccessProfile(): void
    {
        $this->tester->amOnPage('/profile');
        $this->tester->dontSeeAuthentication();
    }

    public function testAnonCantAccessAdmin(): void
    {
        $this->tester->amOnPage('/admin');
        $this->tester->dontSeeAuthentication();
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
     * @return list<User>
     */
    public function getUsers(): array
    {
        return [
            $this->user,
            $this->writer,
            $this->admin,
        ];
    }

    /**
     * @dataProvider getUsers
     */
    public function testUserHasRole(User $user): void
    {
        $this->tester->amLoggedInAs($user);
        $this->tester->amOnPage('/');


        $this->tester->have();

        $this->tester->seeUserHasRole('ROLE_USER');

        if ($user->getEmail() !== 'user@example.com') { // writer and admin
            $this->tester->seeUserHasRole('ROLE_WRITER');
        }

        if ($user->getEmail() === 'admin@example.com') {
            $this->tester->seeUserHasRole('ROLE_ADMIN');
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

    public function testSeeUserPasswordDoesNotNeedRehash(): void
    {
        $user = $this->tester->grabEntityFromRepository(User::class, [
            'email' => 'john_doe@gmail.com',
        ]);
        $this->tester->amLoggedInAs($user);
        $this->tester->amOnPage('/dashboard');

        $this->tester->seeUserPasswordDoesNotNeedRehash();
    }
}
