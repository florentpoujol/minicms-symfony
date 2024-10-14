<?php declare(strict_types=1);

namespace App\Tests\Controllers;

use App\Controller\ArticleController;
use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @see ArticleController
 */
final class AdminArticlesControllerTest extends WebTestCase
{
    private readonly KernelBrowser $client;
    private static User $writer;
    private static User $admin;
    private readonly ArticleRepository $articleRepository;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        \assert($userRepository instanceof UserRepository);

        self::$writer = $userRepository->findOneByOrThrow(['email' => 'writer@example.com']);
        self::$admin = $userRepository->findOneByOrThrow(['email' => 'admin@example.com']);

        $repo = $entityManager->getRepository(Article::class);
        \assert($repo instanceof ArticleRepository);
        $this->articleRepository = $repo;
    }

    private function getResponseContent(): string
    {
        return (string) $this->client->getResponse()->getContent();
    }

    public function testWriterOnlySeeTheirArticlesInTheList(): void
    {
        // act
        $crawler = $this->client
            ->loginUser(self::$writer)
            ->request(Request::METHOD_GET, '/admin/articles');

        // assert
        self::assertResponseIsSuccessful();
        self::assertRouteSame('admin_articles_list');

        $content = $this->getResponseContent();
        self::assertStringNotContainsString('admin@example.com', $content);

        $tableRowCount = $crawler->filter('tr')->count();
        self::assertSame(4, $tableRowCount); // header line + 3 articles
    }

    public function testAdminSeeAllArticlesInTheList(): void
    {
        // act
        $crawler = $this->client
            ->loginUser(self::$admin)
            ->request(Request::METHOD_GET, '/admin/articles');

        // assert
        self::assertResponseIsSuccessful();
        self::assertRouteSame('admin_articles_list');

        $content = $this->getResponseContent();
        self::assertStringContainsString('writer@example.com', $content);
        self::assertStringContainsString('admin@example.com', $content);

        $tableRowCount = $crawler->filter('tr')->count();
        self::assertSame(5, $tableRowCount); // header line + 4 articles
    }

    // --------------------------------------------------

    /**
     * @return iterable<string, array<Closure(): User>>
     */
    public static function getAllUsers(): iterable
    {
        yield 'writer' => [fn (): User => self::$writer];
        yield 'admin' => [fn (): User => self::$admin];
    }

    /**
     * @dataProvider getAllUsers
     *
     * @param Closure(): User $userProvider
     */
    public function testWeCanSeeTheCreationForm(Closure $userProvider): void
    {
        // act
        $crawler = $this->client
            ->loginUser($userProvider())
            ->request(Request::METHOD_GET, '/admin/articles/create');

        // assert
        self::assertResponseIsSuccessful();
        self::assertRouteSame('admin_articles_create');

        self::assertSame(1, $crawler->filter('#article_form')
        ->count());
    }

    /**
     * @dataProvider getAllUsers
     *
     * @param Closure(): User $userProvider
     */
    public function testTheEditFormFillsProperly(Closure $userProvider): void
    {
        // arrange
        $firstWriterArticle = $this->articleRepository->getAllForAdminSection(self::$writer)[0] ?? null;
        \assert($firstWriterArticle instanceof Article);

        // act
        $crawler = $this->client
            ->loginUser(self::$admin)
            ->request(Request::METHOD_GET, '/admin/articles/' . $firstWriterArticle->getSlug() . '/edit');

        // assert
        self::assertResponseIsSuccessful();
        self::assertRouteSame('admin_articles_edit');

        self::assertSame(1, $crawler->filter('#article_form')
            ->count());
    }
}
