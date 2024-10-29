<?php declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use App\Entity\Article;
use App\Entity\User;
use App\Serializer\Normalizer\AuditLogDataNormalizer;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AuditLogDataNormalizerTest extends KernelTestCase
{
    public function test_normalize_an_article(): void
    {
        // arrange
        $article = new Article();
        $article->setId(1);
        $article->setTitle('The Title');
        $article->setSlug('the-title');
        $article->setContent('the content');
        $article->setAllowComments(true);
        $datetime = new DateTimeImmutable('2024-10-29T10:52:00', new \DateTimeZone('+01:00'));
        $article->setCreatedAt(clone $datetime);
        $article->setUpdatedAt(clone $datetime);

        $user = new User();
        $user->setId(2);
        $user->setEmail('the email');
        $user->setPassword('whatever');

        $article->setUser($user);

        $expectedData = [
            'id' => 1,
            'title' => 'The Title',
            'slug' => 'the-title',
            'content' => 'the content',
            'allowComments' => true,
            'publishedAt' => null,
            'createdAt' => '2024-10-29T10:52:00+01:00',
            'updatedAt' => '2024-10-29T10:52:00+01:00',
            'user' => 2,
        ];

        // act
        $symfonyNormalizer = self::getContainer()->get(NormalizerInterface::class);
        $normalizer = new AuditLogDataNormalizer($symfonyNormalizer);

        $actualData = $normalizer->normalize($article);

        // assert
        self::assertSame($expectedData, $actualData);
    }

    public function test_normalize_a_user(): void
    {
        // arrange
        $user = new User();
        $user->setId(1);
        $user->setEmail('the email');
        $user->setPassword('whatever');
        $user->setRoles(['ROLE_USER', 'ROLE_WRITER']);
        $datetime = new DateTimeImmutable('2024-10-29T10:52:00', new \DateTimeZone('+01:00'));
        $user->setCreatedAt(clone $datetime);
        $user->setUpdatedAt(clone $datetime);

        $article = new Article();
        $article->setId(2);
        $article->setTitle('The Title');
        $article->setSlug('the-title');
        $article->setContent('the content');
        $article->setAllowComments(true);
        $user->addArticle($article);

        $article = new Article();
        $article->setId(2);
        $user->addArticle($article);

        $expectedData = [
            'id' => 1,
            'email' => 'the email',
            'roles' => ['ROLE_USER', 'ROLE_WRITER'],
            'password' => 'whatever',
            'verified' => false,
            'createdAt' => '2024-10-29T10:52:00+01:00',
            'updatedAt' => '2024-10-29T10:52:00+01:00',

            // these fields exist in the output, but we don't want them
            'isVerified' => false,
            'userIdentifier' => 'the email',
            'created_at' => '2024-10-29T10:52:00+01:00',
            'updated_at' => '2024-10-29T10:52:00+01:00',
            'writer' => true,
            'admin' => false,
        ];

        // act
        $symfonyNormalizer = self::getContainer()->get(NormalizerInterface::class);
        $normalizer = new AuditLogDataNormalizer($symfonyNormalizer);

        $actualData = $normalizer->normalize($user);

        // assert
        self::assertEquals($expectedData, $actualData);
    }
}
