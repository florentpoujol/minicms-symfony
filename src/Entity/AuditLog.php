<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;
use \App\Enums\AuditLogAction;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: AuditLogAction::class)]
    private ?AuditLogAction $action = null;

    #[ORM\Column(length: 65_635)]
    private ?string $context = null;

    #[ORM\Column]
    /**
     * @var array{before?: array<mixed>, after?: array<mixed>}
     */
    private array $data = [];

    #[ORM\Column]
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

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(string $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return array{before?: array<mixed>, after?: array<mixed>}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array{before?: array<mixed>, after?: array<mixed>} $data
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
}
