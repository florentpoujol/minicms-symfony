<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    private ObjectManager $manager;

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->loadUsers();
        $this->loadArticles();

        $manager->flush();
    }

    private function loadUsers(): void
    {
        $now = new \DateTimeImmutable();

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'user'));
        $user->setVerified(true);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $this->manager->persist($user);

        $user = new User();
        $user->setEmail('writer@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'writer'));
        $user->setRoles(['ROLE_WRITER']);
        $user->setVerified(true);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $this->manager->persist($user);

        $this->setReference('writer', $user);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setVerified(true);
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        $this->manager->persist($user);
    }

    private function loadArticles(): void
    {
        $now = new \DateTimeImmutable();
        $writer = $this->getReference('writer');
        \assert($writer instanceof User);

        $article = new Article();
        $article->setTitle('my first article');
        $article->setSlug('my-first-article');
        $article->setContent(<<<TXT
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus in efficitur enim. Suspendisse vitae enim in dui ornare sollicitudin. Pellentesque nisl felis, tempor vel urna sit amet, tempor blandit ligula. Sed elementum faucibus vehicula. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Phasellus ornare pretium tellus, vitae sollicitudin nisi tincidunt sed. Aliquam iaculis, arcu sed tincidunt fermentum, mauris ligula tincidunt sem, ac aliquet felis ligula eu ante. Nulla non placerat dolor. Pellentesque pulvinar, orci eget pulvinar scelerisque, libero ipsum rhoncus sapien, eget lobortis quam turpis eu nisi. Nunc ante urna, consequat ornare malesuada eget, ultrices nec arcu. Nunc suscipit ac augue ac sagittis. Nunc feugiat tempus nunc vel ornare. Vestibulum elementum eros id dui lacinia faucibus. Suspendisse pellentesque massa sed justo fringilla, ac convallis quam sodales. Aliquam interdum eleifend suscipit. Vivamus molestie rhoncus mauris, ut elementum nisl.

        Suspendisse potenti. Nam pretium augue id nunc convallis dapibus. Aenean finibus tristique ante at volutpat. Mauris vehicula feugiat pulvinar. Aliquam nec eros quis augue faucibus commodo at quis odio. Curabitur sed accumsan eros. Aenean eleifend porta nulla sed porta.

        Cras rhoncus, arcu in eleifend accumsan, est neque rutrum odio, id commodo turpis ante eget quam. Suspendisse posuere bibendum lacus sit amet pharetra. Nunc a magna massa. Vivamus pulvinar eget magna non auctor. Nunc a dolor vitae purus pharetra facilisis non et tortor. Nulla sed aliquet justo, non ornare elit. Mauris ornare, magna vitae facilisis lobortis, felis velit fermentum neque, nec venenatis nisl eros eget enim. Maecenas fermentum mauris quis velit dapibus, sit amet tincidunt est condimentum. Mauris euismod eros vel ante feugiat, sit amet molestie eros rutrum. Etiam condimentum eros massa, sed ullamcorper leo elementum id. Ut eget gravida purus. Donec vitae velit eleifend dolor vulputate semper. Morbi non tristique dolor. Vestibulum ut mollis sem. Curabitur varius consequat lectus eu accumsan.
        TXT);
        $article->setPublishedAt($now);
        $article->setCreatedAt($now);
        $article->setUpdatedAt($now);
        $article->setUser($writer);
        $this->manager->persist($article);

        $article = new Article();
        $article->setTitle('my other article');
        $article->setSlug('my-other-article');
        $article->setContent(<<<TXT
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus in efficitur enim. Suspendisse vitae enim in dui ornare sollicitudin. Pellentesque nisl felis, tempor vel urna sit amet, tempor blandit ligula. Sed elementum faucibus vehicula. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Phasellus ornare pretium tellus, vitae sollicitudin nisi tincidunt sed. Aliquam iaculis, arcu sed tincidunt fermentum, mauris ligula tincidunt sem, ac aliquet felis ligula eu ante. Nulla non placerat dolor. Pellentesque pulvinar, orci eget pulvinar scelerisque, libero ipsum rhoncus sapien, eget lobortis quam turpis eu nisi. Nunc ante urna, consequat ornare malesuada eget, ultrices nec arcu. Nunc suscipit ac augue ac sagittis. Nunc feugiat tempus nunc vel ornare. Vestibulum elementum eros id dui lacinia faucibus. Suspendisse pellentesque massa sed justo fringilla, ac convallis quam sodales. Aliquam interdum eleifend suscipit. Vivamus molestie rhoncus mauris, ut elementum nisl.

        Suspendisse potenti. Nam pretium augue id nunc convallis dapibus. Aenean finibus tristique ante at volutpat. Mauris vehicula feugiat pulvinar. Aliquam nec eros quis augue faucibus commodo at quis odio. Curabitur sed accumsan eros. Aenean eleifend porta nulla sed porta.
        TXT);
        $article->setPublishedAt($now->modify('+ 1 day'));
        $article->setCreatedAt($now->modify('+ 1 day'));
        $article->setUpdatedAt($now->modify('+ 1 day'));
        $article->setUser($writer);
        $this->manager->persist($article);

        $article = new Article();
        $article->setTitle('my draft article');
        $article->setSlug('my-draft-article');
        $article->setContent(<<<TXT
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus in efficitur enim. Suspendisse vitae enim in dui ornare sollicitudin. Pellentesque nisl felis, tempor vel urna sit amet, tempor blandit ligula. Sed elementum faucibus vehicula. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Phasellus ornare pretium tellus, vitae sollicitudin nisi tincidunt sed. Aliquam iaculis, arcu sed tincidunt fermentum, mauris ligula tincidunt sem, ac aliquet felis ligula eu ante. Nulla non placerat dolor. Pellentesque pulvinar, orci eget pulvinar scelerisque, libero ipsum rhoncus sapien, eget lobortis quam turpis eu nisi. Nunc ante urna, consequat ornare malesuada eget, ultrices nec arcu. Nunc suscipit ac augue ac sagittis. Nunc feugiat tempus nunc vel ornare. Vestibulum elementum eros id dui lacinia faucibus. Suspendisse pellentesque massa sed justo fringilla, ac convallis quam sodales. Aliquam interdum eleifend suscipit. Vivamus molestie rhoncus mauris, ut elementum nisl.
        TXT);
        $article->setPublishedAt(null);
        $article->setCreatedAt($now);
        $article->setUpdatedAt($now);
        $article->setUser($writer);
        $this->manager->persist($article);
    }
}
