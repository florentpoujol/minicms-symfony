<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;
use Twig\TwigFilter;

final class BlogController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly Environment $twig,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        $this->twig->addFilter(new TwigFilter('show_excerpt', fn (string $text): string => substr($text, 0, 500)));

        return $this->render('blog/index.html.twig', [
            'articles' => $this->articleRepository->getAllPublished(),
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_article_show', requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showArticle(
        #[MapEntity(mapping: ['slug' => 'slug'])] // note: from the documentation, this should work without the explicit mapping
        Article $article,
        #[CurrentUser]
        ?User $user,
    ): Response {
        if (
            $user === null &&
            ($article->getPublishedAt() === null || $article->getPublishedAt() > new \DateTimeImmutable())
        ) {
            return $this->render('blog/not-found.html.twig', [], new Response(status: Response::HTTP_NOT_FOUND));
        }

        return $this->render('blog/article.html.twig', [
            'article' => $article,
        ]);
    }
}
