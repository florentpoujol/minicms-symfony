<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('a');
        // $user->setRoles([]);
        $user->setVerified(true);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('writer@example.com');
        $user->setPassword('b');
        $user->setRoles(['ROLE_WRITER']);
        $user->setVerified(true);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPassword('c');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setVerified(true);
        $manager->persist($user);

        $manager->flush();
    }
}
