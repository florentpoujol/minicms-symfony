<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Message\TestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(
        #[CurrentUser] User $user,
        MessageBusInterface $bus,
    ): Response
    {
        $bus->dispatch(new TestMessage());
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'isWriter' => $user->isWriter(),
        ]);
    }

    #[AsMessageHandler]
    public function messageHandler(TestMessage $msg)
    {
        $logger->info("handled message ");
    }
}
