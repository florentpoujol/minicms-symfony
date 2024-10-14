<?php declare(strict_types=1);

namespace App\Tests\Controllers;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BlogControllerTest extends WebTestCase
{
    private readonly KernelBrowser $client;
    private readonly User $writer;
    private readonly ArticleRepository $articleRepository;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'writer@example.com']);
        \assert($user instanceof User);
        $this->writer = $user;

        $repo = $entityManager->getRepository(Article::class);
        \assert($repo instanceof ArticleRepository);
        $this->articleRepository = $repo;
    }

    private function getResponseContent(): string
    {
        return (string) $this->client->getResponse()->getContent();
    }

    public function testWeCanSeeTheHomePageWithArticles(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('h1', 'Articles');

        self::assertAnySelectorTextContains('h2', 'my first article');

        self::assertAnySelectorTextContains('h2', 'my other article');
        $firstLink = $crawler->filter('a[class=read-more-link]')->attr('href');
        self::assertSame('/blog/my-other-article', $firstLink); // this one is the first link on the page because

        // not published yet or published with a date in the future
        self::assertAnySelectorTextNotContains('h2', 'my article in the future');
        self::assertAnySelectorTextNotContains('h2', 'my draft article');
    }

    public function testWeCanSeeAnArticlePageWhenPublished(): void
    {
        // arrange
        $article = $this->articleRepository->findOneBy(['slug' => 'my-first-article']);
        self::assertInstanceOf(Article::class, $article);

        // act
        $this->client->request(Request::METHOD_GET, '/blog/' . $article->getSlug());

        // assert
        self::assertResponseIsSuccessful();

        self::assertAnySelectorTextContains('h1', $article->getTitle());

        self::assertAnySelectorTextContains('aside', 'Published on lundi 23 septembre 2024 à 13:14:03 by writer@example.com');

        self::assertStringContainsString($article->getContent(), $this->getResponseContent()); // can't use assertAnySelectorTextContains() here, I think it doesn't properly handle the carriage return
    }

    public function testGuestCantSeeArticleWhenDraft(): void
    {
        // arrange
        $article = $this->articleRepository->findOneBy(['slug' => 'my-draft-article']);
        self::assertInstanceOf(Article::class, $article);

        // act
        $this->client->request(Request::METHOD_GET, '/blog/' . $article->getSlug());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAnAuthorCanSeeTheirArticlePageEvenWhenNotPublished(): void
    {
        // arrange
        $article = $this->articleRepository->findOneBy(['slug' => 'my-draft-article']);
        self::assertInstanceOf(Article::class, $article);

        // act
        $this->client->loginUser($this->writer);
        $this->client->request(Request::METHOD_GET, '/blog/' . $article->getSlug());

        // assert
        self::assertResponseIsSuccessful();

        self::assertAnySelectorTextContains('h1', $article->getTitle());

        self::assertStringContainsString($article->getContent(), $this->getResponseContent()); // can't use assertAnySelectorTextContains() here, I think it doesn't properly handle the carriage return
    }
}
