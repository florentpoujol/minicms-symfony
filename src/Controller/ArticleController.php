<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\String\Slugger\AsciiSlugger;
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
    public function list(
        #[CurrentUser]
        User $user,
    ): Response
    {
        $this->twig->addFilter(new TwigFilter('strlen', 'strlen'));

        return $this->render('admin/articles/list.html.twig', [
            'articles' => $this->articleRepository->getAllForAdminSection($user),
        ]);
    }

    #[Route('/admin/articles/create', name: 'admin_articles_create')]
    #[Route('/admin/articles/{slug}/edit', name: 'admin_articles_edit', requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function form(
        #[MapEntity(mapping: ['slug' => 'slug'])] ?Article $article,
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        AuditLogRepository $auditLogRepository,
        #[MapQueryParameter] int $auditLogPage = 1,
        #[MapQueryParameter] int $auditLogPerPage = 5,
    ): Response
    {
        $isCreateForm = $article === null;

        $form = $this->createForm(ArticleForm::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Article $article */
            $article = $form->getData();

            if ($isCreateForm) {
                $slugger = new AsciiSlugger();
                $slug = $slugger->slug($article->getTitle())->lower()->toString();
                $article->setSlug($slug);

                $article->setUser($user);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('admin_articles_edit', ['slug' => $article->getSlug()]);
        }

        return $this->render('admin/articles/form.html.twig', [
            'form' => $form,
            'article' => $article,
            'isCreateForm' => $isCreateForm,
            'auditLogs' => [
                'results' => $article !== null ? $auditLogRepository->getForEntity($article, $auditLogPage, $auditLogPerPage) : [],
                'page' => $auditLogPage,
                'perPage' => $auditLogPerPage,
            ],
        ]);
    }
}
