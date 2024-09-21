<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\TwigFilter;

final class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly Environment $twig,
    ) {
    }

    #[Route('/admin/articles', name: 'admin_articles_list')]
    public function list(): Response
    {
        $this->twig->addFilter(new TwigFilter('strlen', 'strlen'));

        $user = $this->getUser();
        \assert($user instanceof User);

        return $this->render('admin/articles/list.html.twig', [
            'articles' => $this->articleRepository->getAllForAdmin($user),
        ]);
    }
}
