<?php declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use App\Entity\Article;
use App\Entity\User;
use App\Serializer\Normalizer\AuditLogDataNormalizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class AuditLogDataNormalizerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::getContainer()->get(SerializerInterface::class);
    }

    public function test_normalize_and_article(): void
    {
        // arrange
        $article = new Article();
        $article->setId(1);
        $article->setTitle('The Title');
        $article->setSlug('the-title');
        $article->setContent('the content');
        $article->setAllowComments(true);
        $datetime = new \DateTimeImmutable('2024-10-29T10:52:00', new \DateTimeZone('+01:00'));
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
            'user' => 2
        ];

        // act
        /** @var AuditLogDataNormalizer $normalizer */
        $normalizer = self::getContainer()->get('serializer.normalizer.auditlog');

        $actualData = $normalizer->normalize($article);

        // assert
        self::assertTrue($normalizer->supportsNormalization($article));

        self::assertSame($expectedData, $actualData);
    }
}
