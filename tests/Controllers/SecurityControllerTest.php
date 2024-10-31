<?php declare(strict_types=1);

namespace App\Tests\Controllers ;

use App\Entity\User;
use App\Repository\UserRepository;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class SecurityControllerTest extends WebTestCase
{
    private readonly KernelBrowser $client;
    private static User $user;
    private static User $writer;
    private static User $admin;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        \assert($userRepository instanceof UserRepository);

        // the variable here must be static, otherwise PHP complain that within the tests we access the properties
        // before theirs initialisation, even when the data provider return a closure...
        self::$user = $userRepository->findOneByOrThrow(['email' => 'user@example.com']);
        self::$writer = $userRepository->findOneByOrThrow(['email' => 'writer@example.com']);
        self::$admin = $userRepository->findOneByOrThrow(['email' => 'admin@example.com']);
    }

    /**
     * @return array<string, list<Closure(): User|string>>
     */
    public static function getAllUsers(): array
    {
        return [
            'user' => [fn (): User => self::$user, 'user'],
            'writer' => [fn (): User => self::$writer, 'writer'],
            'admin' => [fn (): User => self::$admin, 'admin'],
        ];
    }

    public function testAnonCantAccessProfileOrAdmin(): void
    {
        $this->client->request(Request::METHOD_GET, '/profile');
        self::assertResponseRedirects('/login');

        $this->client->request(Request::METHOD_GET, '/admin');
        self::assertResponseRedirects('/login');
    }

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $userReturner
     */
    public function testUsersCanAccessProfile(Closure $userReturner): void
    {
        $user = $userReturner();

        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/profile');

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $userReturner
     */
    public function testUserCanAccessAdminOrNot(Closure $userReturner): void
    {
        $user = $userReturner();
        \assert($user instanceof User);

        // act
        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/admin/articles');

        // assert
        if ($user->getEmail() === 'user@example.com') {
            self::assertResponseStatusCodeSame(403);
        } else {
            self::assertResponseIsSuccessful();
        }
    }

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $userReturner
     */
    public function testUserHasRole(Closure $userReturner): void
    {
        // arrange
        $user = $userReturner();
        \assert($user instanceof User);

        // act
        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/');

        // assert
        $roles = $user->getRoles();

        // for every one because always added by Symfony
        self::assertTrue(\in_array('ROLE_USER', $roles, true));

        if ($user->getEmail() === 'writer@example.com') {
            self::assertTrue(\in_array('ROLE_WRITER', $roles, true));
            self::assertFalse(\in_array('ROLE_ADMIN', $roles, true));
        }

        if ($user->getEmail() === 'admin@example.com') {
            self::assertTrue(\in_array('ROLE_ADMIN', $roles, true));
            self::assertFalse(\in_array('ROLE_WRITER', $roles, true));
        }
    }

    // --------------------------------------------------

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $getUser
     */
    public function testRegisterWhenLoggedInRedirectsToProfile(Closure $getUser): void
    {
        $user = $getUser();
        \assert($user instanceof User);

        $this->client
            ->loginUser($user)
            ->request(Request::METHOD_GET, '/register');

        self::assertResponseRedirects('/profile');
    }

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $getUser
     */
    public function testLoginWhenLoggedInRedirectsToProfile(Closure $getUser): void
    {
        $user = $getUser();
        \assert($user instanceof User);

        $this->client
            ->loginUser($user)
            ->request(Request::METHOD_GET, '/login');

        self::assertResponseRedirects('/profile');
    }

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $getUser
     */
    public function testLogoutRedirectsToHome(Closure $getUser): void
    {
        $this->client
            ->loginUser($getUser())
            ->request(Request::METHOD_GET, '/logout');

        self::assertResponseRedirects('/');
    }

    #[DataProvider('getAllUsers')]
    /**
     * @param Closure(): User $getUser
     */
    public function testVerifyEmailRedirectsToHome(Closure $getUser): void
    {
        $this->client->followRedirects();
        $this->client
            ->loginUser($getUser())
            ->request(Request::METHOD_GET, '/verify/email');

        self::assertResponseIsSuccessful(); // because we are now on the /profile page after being redirected to /redirect first
        self::assertRouteSame('app_profile');
    }
}
