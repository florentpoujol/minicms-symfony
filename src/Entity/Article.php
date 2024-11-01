<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArticleRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: '`article`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_SLUG', fields: ['slug'])]
#[UniqueEntity(fields: ['slug'], message: 'There is already an article with this title')]
#[ORM\HasLifecycleCallbacks]
class Article implements DoctrineEntity
{
    use HasAutomaticTimestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(['audit_log.article', 'audit_log.when_entity_relation'])]
    private int $id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[ORM\Column(length: 255)]
    #[Serializer\Groups(['audit_log.article'])]
    private string $title;

    #[ORM\Column(length: 255)]
    #[Serializer\Groups(['audit_log.article'])]
    private string $slug;

    #[Assert\NotBlank]
    #[Assert\Length(max: 99_999)]
    #[ORM\Column(type: Types::TEXT)]
    #[Serializer\Groups(['audit_log.article'])]
    private string $content;

    #[ORM\Column]
    #[Serializer\Groups(['audit_log.article'])]
    private bool $allow_comments = true;

    #[ORM\Column(nullable: true)]
    #[Serializer\Groups(['audit_log.article'])]
    private ?DateTimeImmutable $published_at = null;

    #[ORM\Column(updatable: false, columnDefinition: "DATETIME not null default current_timestamp")]
    #[Serializer\Groups(['audit_log.article'])]
    private DateTimeImmutable $created_at;

    #[ORM\Column(columnDefinition: "DATETIME not null default current_timestamp on update current_timestamp")]
    #[Serializer\Groups(['audit_log.article'])]
    private DateTimeImmutable $updated_at;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Serializer\Groups(['audit_log.article'])]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function isAllowComments(): bool
    {
        return $this->allow_comments;
    }

    public function setAllowComments(bool $allow_comments): static
    {
        $this->allow_comments = $allow_comments;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(?DateTimeImmutable $published_at): static
    {
        $this->published_at = $published_at;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
