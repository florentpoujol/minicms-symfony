<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AuditLog;
use App\Entity\User;
use App\Enums\AuditLogAction;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
/**
 * TODO:
 * - for one to many relation ship, only save the identifier, not the whole object
 * - ideally do not save other relationships without the Ignore attribute on theses
 * - save properties for before / after
 * - for the article > user relationship, see why all properties + getters are serialized, which doesn't happen for the article
 */
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
        private readonly NormalizerInterface $normalizer,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
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
        static $i;
        $i++;
        $entity = $event->getObject();
        $this->logger->info($entity::class, ['i' => $i]); // doesn't work ?

        if (\in_array($entity::class, $this->ignoredEntities, true)) {
            return;
        }

        $log = new AuditLog();
        // $log->model()->associate($model);
        $log->setAction($action);
        $log->setContext('{unknown}');
        $log->setCreatedAt(new \DateTimeImmutable());

        if (\PHP_SAPI === 'cli') {
            // get the full actual CLI command entered in the terminal, which gives the options and arguments
            $args = implode(' ', $_SERVER['argv'] ?? []);
            $log->setContext("cli: ($args)");

            // could also get the name of the current job, if possible (probably need the same shenanigans as for the Artisan command name)
        } else { // probably web
            $user = $this->tokenStorage->getToken()->getUser(); // using the CurrentUser attribute didn't work...
            if ($user instanceof User) {
                $log->setUser($user);
            }

            $request = $this->requestStack->getCurrentRequest();
            if ($request instanceof Request) {
                $log->setContext('http:' . $request->getPathInfo() . '?' . $request->getQueryString());
            }
        }

        $data = [];
        if ($action === AuditLogAction::CREATE) {
            $data['after'] = $this->normalizer->normalize($entity, 'array');
        } elseif ($action === AuditLogAction::DELETE) {
            $data['before'] = $this->normalizer->normalize($entity, 'array');
        } elseif ($action === AuditLogAction::UPDATE) {
            $data['before'] = [];
            $data['after'] = [];

            assert($event instanceof PreUpdateEventArgs);
            $changeSet = $event->getEntityChangeSet();

            /**
             * @var string $property
             * @var array{0: mixed, 1: mixed} $beforeAndAfter 0 is before, 1 is after
             */
            foreach ($changeSet as $property => $beforeAndAfter) {
                $data['before'][$property] = $beforeAndAfter[0];
                $data['after'][$property] = $beforeAndAfter[1];
            }

            // FIXME florent: there is an infinite loop until out of memory
            //  The audit_log table auto increment values show that this is due to a loop of AuditLog creation

        }
        $log->setData($data); // @phpstan-ignore-line

        // remove sensitive properties ? (maybe done by the serializer)
        // ideally we would like to keep the sensitive properties, but redact their values

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
