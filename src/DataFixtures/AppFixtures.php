<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'user'));
        $user->setVerified(true);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('writer@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'writer'));
        $user->setRoles(['ROLE_WRITER']);
        $user->setVerified(true);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setVerified(true);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $manager->persist($user);

        $manager->flush();
    }
}
