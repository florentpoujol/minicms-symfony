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

    public function testWeCanCreateANewArticle(): void
    {
        // arrange
        $newArticle = $this->articleRepository->findOneBy(['slug' => 'the-new-test-title']);
        self::assertNull($newArticle);

        // act
        $crawler = $this->client
            ->loginUser(self::$writer)
            ->request(Request::METHOD_GET, '/admin/articles/create');

        $form = $crawler
            ->selectButton('article_form_save')
            ->form();

        $form->setValues([
            'article_form[title]' => 'The New Test Title',
            'article_form[content]' => 'the new test content',
            // 'article_form[allow_comments]' => '0',
            'article_form[published_at]' => '2024-10-14T15:53',
        ]);
        $this->client->submit($form);

        // assert
        $newArticle = $this->articleRepository->findOneBy(['slug' => 'the-new-test-title']);
        self::assertInstanceOf(Article::class, $newArticle);

        self::assertSame('The New Test Title', $newArticle->getTitle());
        self::assertSame('the-new-test-title', $newArticle->getSlug());
        self::assertSame('the new test content', $newArticle->getContent());
        self::assertSame(false, $newArticle->isAllowComments());
        self::assertSame('2024-10-14 15:53:00', $newArticle->getPublishedAt()?->format('Y-m-d H:i:s'));
    }

    public function testTheEditFormFillsProperly(): void
    {
        // arrange
        $article = $this->articleRepository->getAllForAdminSection(self::$writer)[0] ?? null;
        \assert($article instanceof Article);

        // act
        $crawler = $this->client
            ->loginUser(self::$admin)
            ->request(Request::METHOD_GET, '/admin/articles/' . $article->getSlug() . '/edit');

        // assert
        self::assertResponseIsSuccessful();
        self::assertRouteSame('admin_articles_edit');

        $value = $crawler->filter('#article_form_title')->attr('value');
        self::assertSame($article->getTitle(), $value);

        $value = $crawler->filter('#article_form_content');
        self::assertSame($article->getContent(), $value->text());

        $value = $crawler->filter('#article_form_allow_comments')->attr('value');
        self::assertSame($article->isAllowComments(), (bool) $value);

        $value = $crawler->filter('#article_form_published_at')->attr('value');
        self::assertSame($article->getPublishedAt()?->format('Y-m-d\TH:i'), $value);
    }
}
