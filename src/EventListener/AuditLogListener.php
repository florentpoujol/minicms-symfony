<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AuditLog;
use App\Entity\User;
use App\Enums\AuditLogAction;
use App\Serializer\Normalizer\AuditLogDataNormalizer;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
final class AuditLogListener
{
    /**
     * @var array<class-string>
     */
    private array $ignoredEntities = [
        AuditLog::class,
    ];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly AuditLogDataNormalizer $normalizer,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $this->saveLog($event, AuditLogAction::CREATE);
    }

    /**
     * Ideally here, we should use the postUpdate event so that we register the log only
     * when we are sure the entity is updated, but the change set with the properties before and after arent't available
     * in the post update event.
     */
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $this->saveLog($event, AuditLogAction::UPDATE);
    }

    public function postRemove(PostRemoveEventArgs $event): void
    {
        $this->saveLog($event, AuditLogAction::DELETE);
    }

    /**
     * @param LifecycleEventArgs<ObjectManager> $event
     */
    private function saveLog(LifecycleEventArgs $event, AuditLogAction $action): void
    {
        $entity = $event->getObject();
        if (\in_array($entity::class, $this->ignoredEntities, true)) {
            return;
        }

        $context = '{unknown}';
        $user = null;
        if (\PHP_SAPI === 'cli') {
            // get the full actual CLI command entered in the terminal, which gives the options and arguments
            $args = implode(' ', $_SERVER['argv'] ?? []);
            $context = "cli: $args";

            // could also get the name of the current job, if possible (probably need the same shenanigans as for the Artisan command name)
        } else { // probably web
            $loggedInUser = $this->tokenStorage->getToken()?->getUser();
            if ($loggedInUser instanceof User) {
                $user = $loggedInUser;
            }

            $request = $this->requestStack->getCurrentRequest();
            if ($request instanceof Request) {
                $queryString = $request->getQueryString();
                if ($queryString !== null && $queryString !== '') {
                    $queryString = "?$queryString";
                }

                $context = 'http: ' . $request->getPathInfo() . $queryString;
            }
        }

        $data = [];
        // $this->normalizer is App\Serializer\Normalizer\AuditLogDataNormalizer
        if ($action === AuditLogAction::CREATE) {
            $data['after'] = $this->normalizer->normalize($entity);
        } elseif ($action === AuditLogAction::DELETE) {
            $data['before'] = $this->normalizer->normalize($entity);
        } elseif ($action === AuditLogAction::UPDATE) {
            $data['before'] = [];
            $data['after'] = [];

            \assert($event instanceof PreUpdateEventArgs);
            $changeSet = $event->getEntityChangeSet();

            /**
             * @var string $property
             * @var array{0: mixed, 1: mixed} $beforeAndAfter 0 is before, 1 is after
             */
            foreach ($changeSet as $property => $beforeAndAfter) {
                $data['before'][$property] = $beforeAndAfter[0];
                $data['after'][$property] = $beforeAndAfter[1];
            }
        }

        // remove sensitive properties ? (maybe done by the serializer)
        // ideally we would like to keep the sensitive properties, but redact their values

        // Calling flush() here (inside a doctrine lifecycle event listener) is "strongly discouraged" by the doc
        // https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/events.html#events-overview.
        // Doing it during pre update cause an infinite loop where the method is called again and again with the same article and a new AuditLog every time.
        // Not calling flush() however do not cause the AuditLog to be persisted.
        // $this->entityManager->persist($log);
        // $this->entityManager->flush();

        $log = new AuditLog();
        $log->setEntity($entity);

        // So instead, we are saving the log "manually"
        $this->entityManager
            ->getConnection()
            ->executeQuery(<<<SQL
                insert into audit_log (user_id, action, context, data, created_at, entity_id, entity_type)
                values (:user_id, :action, :context, :data, :created_at, :entity_id, :entity_type)
                SQL,
                [
                    'user_id' => $user?->getId(),
                    'action' => $action->value,
                    'context' => $context,
                    'data' => json_encode($data),
                    'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s.u'),
                    'entity_id' => $log->getEntityId(),
                    'entity_type' => $log->getEntityType(),
                ],
            );
    }
}
