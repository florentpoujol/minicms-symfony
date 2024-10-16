<?php declare(strict_types=1);

namespace App\Entity;

use App\Enums\AuditLogAction;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(enumType: AuditLogAction::class)]
    private ?AuditLogAction $action = null;

    #[ORM\Column(length: 1_000)]
    private string $context = '';

    #[ORM\Column(type: 'json')]
    /**
     * @var array{before?: array<string, mixed>, after?: array<string, mixed>} $data
     */
    private array $data = []; // @phpstan-ignore-line (still gives the "...  no value type specified ..." error)

    #[ORM\Column(updatable: false, columnDefinition: "TIMESTAMP(2) default current_timestamp")]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'auditLogs')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?AuditLogAction
    {
        return $this->action;
    }

    public function setAction(AuditLogAction $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setContext(string $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return array{before?: array<string, mixed>, after?: array<string, mixed>}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array{before?: array<string, mixed>, after?: array<string, mixed>} $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    //--------------------------------------------------

    /**
     * @var array<int, class-string>
     */
    private static array $morphMap = [
        1 => User::class,
        2 => Article::class,
    ];

    #[ORM\Column]
    private int $entity_id;

    #[ORM\Column(type: 'smallint')]
    private int $entity_type;

    public function getEntityId(): int
    {
        return  $this->entity_id;
    }

    public function getEntityType(): int
    {
        return  $this->entity_type;
    }

    /**
     * @return class-string
     */
    public function getEntityFqcn(): string
    {
        return self::$morphMap[$this->entity_type];
    }

    public static function getTypeForEntity(string $entityFqcn): int
    {
        $typeId = array_search($entityFqcn, self::$morphMap, true);
        if (!\is_int($typeId)) {
            throw new \UnexpectedValueException("No type id found for class '$entityFqcn'");
        }

        return $typeId;
    }

    public function setEntity(object $entity): void
    {
        $entityFqcn = $entity::class;
        $this->entity_type =  self::getTypeForEntity($entityFqcn);

        if (!method_exists($entity, 'getId')) {
            throw new \UnexpectedValueException("No method getId() found on class '$entityFqcn'");
        }

        $entityId = $entity->getId();
        if ($entityId === null) {
            throw new \UnexpectedValueException("Entity of type '$entityFqcn' has no id defined");
        }

        $this->entity_id = $entityId;
    }
}
