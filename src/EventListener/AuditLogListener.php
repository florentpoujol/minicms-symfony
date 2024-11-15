<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AuditLog;
use App\Entity\DoctrineEntity;
use App\Entity\User;
use App\Enums\AuditLogAction;
use DateTimeImmutable;
use DateTimeInterface;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
final class AuditLogListener
{
    /**
     * @var array<class-string<DoctrineEntity>>
     */
    private const array IGNORED_ENTITIES = [
        AuditLog::class,
    ];

    /**
     * @var array<class-string<DoctrineEntity>, array<string>>
     */
    private const array OBFUSCATED_PROPERTIES = [
        User::class => ['password'],
    ];

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly NormalizerInterface $normalizer,
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

    //--------------------------------------------------

    /**
     * @param LifecycleEventArgs<ObjectManager> $event
     */
    private function saveLog(LifecycleEventArgs $event, AuditLogAction $action): void
    {
        $entity = $event->getObject();
        if (\in_array($entity::class, self::IGNORED_ENTITIES, true)) {
            return;
        }
        \assert($entity instanceof DoctrineEntity);

        $context = '{unknown}';
        $user = null;
        if (\PHP_SAPI === 'cli') {
            // get the full actual CLI command entered in the terminal, which gives the options and arguments
            $args = $_SERVER['argv'] ?? [];
            \assert(\is_array($args));
            $args = implode(' ', $args);
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

        $paths = explode('\\', $entity::class);
        $entityName = strtolower(end($paths));
        $serializationContext = [
            AbstractNormalizer::GROUPS => [
                "audit_log.$entityName", // the main entity we want to serialize
                'audit_log.when_entity_relation', // this allows to serialize entity relation's but only their PK
            ],
        ];

        if ($action === AuditLogAction::CREATE) {
            $data['after'] = $this->normalizer->normalize($entity, context: $serializationContext);
        } elseif ($action === AuditLogAction::DELETE) {
            $data['before'] = $this->normalizer->normalize($entity, context: $serializationContext);
        } elseif ($action === AuditLogAction::UPDATE) {
            \assert($event instanceof PreUpdateEventArgs);
            $changeSet = $event->getEntityChangeSet();

            $beforeArray = [];
            $afterArray = [];
            /**
             * @var string $property
             * @var array{0: mixed, 1: mixed} $beforeAndAfter 0 is before, 1 is after
             */
            foreach ($changeSet as $property => $beforeAndAfter) {
                // note that here the $property name is the actual property name, so in snake case like "created_at"
                // where for the creation and deletion, the keys would be the camel cased version, like "createdAt"

                if ($beforeAndAfter[0] instanceof DateTimeInterface) {
                    $beforeAndAfter[0] = $beforeAndAfter[0]->format(DateTimeInterface::ATOM);
                }
                $beforeArray[$property] = $beforeAndAfter[0];

                if ($beforeAndAfter[1] instanceof DateTimeInterface) {
                    $beforeAndAfter[1] = $beforeAndAfter[1]->format(DateTimeInterface::ATOM);
                }
                $afterArray[$property] = $beforeAndAfter[1];
            }

            // $beforeEntity = $this->denormalizer->denormalize($beforeArray, $entity::class); // doesn't work because the DateTime denormalizer doesn't expect the value to already be of the correct type

            $data['before'] = $beforeArray;
            $data['after'] = $afterArray;
        }

        // remove sensitive properties
        $obfuscatedProperties = self::OBFUSCATED_PROPERTIES[$entity::class] ?? [];
        if ($obfuscatedProperties !== []) {
            foreach ($obfuscatedProperties as $property) {
                if (isset($data['before'][$property]) && \is_string($data['before'][$property])) {
                    $data['before'][$property] = substr($data['before'][$property], 0, 5) . '(obfuscated)';
                }

                if (isset($data['after'][$property]) && \is_string($data['after'][$property])) {
                    $data['after'][$property] = substr($data['after'][$property], 0, 5) . '(obfuscated)';
                }
            }
        }

        //--------------------------------------------------

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
