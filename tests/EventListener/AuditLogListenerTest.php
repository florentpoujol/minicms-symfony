<?php declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\AuditLog;
use App\Entity\User;
use App\Repository\AuditLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AuditLogListenerTest extends KernelTestCase
{
    private readonly AuditLogRepository $auditLogRepository;
    private readonly EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        \assert($entityManager instanceof EntityManagerInterface); // apparently, the Symfony extension tells PHPStan that the getManager() method return ObjectManager, which is a parent interface to EntityManagerInterface
        $this->entityManager = $entityManager;

        $auditLogRepository = $this->entityManager->getRepository(AuditLog::class);
        \assert($auditLogRepository instanceof AuditLogRepository);
        $this->auditLogRepository = $auditLogRepository;
    }

    public function test_create_a_user(): void
    {
        // arrange
        $user = new User();
        $user->setEmail('the email');
        $user->setPassword('$2y$13$RVzGEbzCN');
        $user->setRoles(['ROLE_USER', 'ROLE_WRITER']);
        $datetime = new DateTimeImmutable('2024-10-29T10:52:00', new \DateTimeZone('+01:00'));
        $user->setCreatedAt(clone $datetime);
        $user->setUpdatedAt(clone $datetime);

        // act
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // assert
        $lastAuditLog = $this->auditLogRepository->getLast();

        self::assertSame(User::class, $lastAuditLog->getEntityFqcn());

        $after = $lastAuditLog->getData()['after'] ?? [];
        self::assertNotEmpty($after);
        self::assertSame('the email', $after['email']);
        self::assertSame('$2y$13$RV...', $after['obfuscatedPassword']);
        self::assertSame(['ROLE_USER', 'ROLE_WRITER'], $after['roles']);
        self::assertFalse($after['isVerified']);
        self::assertSame('2024-10-29T10:52:00+01:00', $after['created_at']);
        self::assertSame('2024-10-29T10:52:00+01:00', $after['updated_at']);
    }
}
