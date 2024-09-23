<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'articles' => $this->articleRepository->getAllPublished(),
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_article_show')]
    public function showArticle(
        #[MapEntity(mapping: ['slug' => 'slug'])] // note: from the documentation, this should work without the explicit mapping
        Article $article
    ): Response {
        return $this->render('blog/article.html.twig', [
            'article' => $article,
        ]);
    }
}
