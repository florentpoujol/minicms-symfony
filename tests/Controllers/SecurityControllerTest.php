<?php declare(strict_types=1);

namespace App\Tests\Controllers ;

use App\Entity\User;
use Closure;
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
        $entityManager = $container->get('doctrine')->getManager(); // @phpstan-ignore-line

        $userRepository = $entityManager->getRepository(User::class);

        // the variable here must be static, otherwise PHP complain that within the tests we access the properties
        // before theirs initialisation, even when the data provider return a closure...
        self::$user = $userRepository->findOneBy(['email' => 'user@example.com']);
        self::$writer = $userRepository->findOneBy(['email' => 'writer@example.com']);
        self::$admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
    }

    /**
     * @return array<string, list<Closure(): User>>
     */
    public function getAllUsers(): array
    {
        return [
            'user' => [fn (): User => self::$user],
            'writer' => [fn (): User => self::$writer],
            'admin' => [fn (): User => self::$admin],
        ];
    }

    public function testAnonCantAccessProfileOrAdmin(): void
    {
        $this->client->request(Request::METHOD_GET, '/profile');
        self::assertResponseRedirects('/login');

        $this->client->request(Request::METHOD_GET, '/admin');
        self::assertResponseRedirects('/login');
    }

    /**
     * @dataProvider getAllUsers
     *
     * @param Closure(): User $userReturner
     */
    public function testUsersCanAccessProfile(Closure $userReturner): void
    {
        $user = $userReturner();

        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/profile');

        self::assertResponseIsSuccessful();
    }

    /**
     * @dataProvider getAllUsers
     *
     * @param Closure(): User $userReturner
     */
    public function testUserCanAccessAdminOrNot(Closure $userReturner): void
    {
        $user = $userReturner();

        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/admin');

        if ($user->getEmail() === 'user@example.com') {
            self::assertResponseStatusCodeSame(403);
        } else {
            self::assertResponseIsSuccessful();
        }
    }

    /**
     * @dataProvider getAllUsers
     *
     * @param Closure(): User $userReturner
     */
    public function testUserHasRole(Closure $userReturner): void
    {
        $user = $userReturner();

        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/');

        $roles = $user->getRoles();

        // for every one because always added by Symfony
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
}
