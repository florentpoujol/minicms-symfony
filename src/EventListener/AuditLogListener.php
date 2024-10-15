<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\AuditLog;
use App\Entity\User;
use App\Enums\AuditLogAction;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
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
        #[CurrentUser]
        private readonly ?User                  $user,
        private readonly ?Request               $request,
        private readonly NormalizerInterface    $normalizer,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $this->saveLog($event, AuditLogAction::CREATE);
    }

    public function postUpdate(PostUpdateEventArgs $event): void
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

        $log = new AuditLog();
        // $log->model()->associate($model);

        $log->setAction($action);

        if (\PHP_SAPI === 'cli') {
            // find the name of the currently running Artisan command...
            // In actual scenario, this is probably super slow and not worth it ?

            $commandName = '{unknown}';

            // get the full actual CLI command entered in the terminal, which gives the options and arguments
            $args = implode(' ', $_SERVER['argv'] ?? []);

            $log->setContext('cli: ' . $commandName . " ($args)");

            // could also get the name of the current job, if possible (probably need the same shenanigans as for the Artisan command name)
        } else { // assume web
            $log->setUser($this->user);
            if ($this->request instanceof Request) {
                $log->setContext('http:' . $this->request->getUri());
            }
        }

        $data = [];
        if ($action === AuditLogAction::CREATE) {
            $data['after'] = $this->normalizer->normalize($entity, 'array');
        } elseif ($action === AuditLogAction::DELETE) {
            $data['before'] = $this->normalizer->normalize($entity, 'array');
        } elseif ($action === AuditLogAction::UPDATE) {
            // keys are the changed attributes
            // values are the current values
            // $data['after'] = $model->getChanges();
            //
            // $data['before'] = [];
            // foreach (array_keys($data['after']) as $changedAttribute) {
            //     $data['before'][$changedAttribute] = $model->getOriginal($changedAttribute);
            // }
        }

        // remove sensitive properties ? (maybe done by the serializer)

        $log->setData($data); // @phpstan-ignore-line
        $log->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
