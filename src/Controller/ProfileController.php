<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(
        #[CurrentUser] User $user,
    ): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'isWriter' => $user->isWriter(),
        ]);
    }
}
