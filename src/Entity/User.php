<?php declare(strict_types=1);

namespace App\Entity;

use App\Enums\SupportedLocale;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface, DoctrineEntity
{
    use HasAutomaticTimestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Serializer\Groups(['audit_log.user', 'audit_log.when_entity_relation'])]
    private int $id;

    #[ORM\Column(length: 180)]
    #[Serializer\Groups(['audit_log.user'])]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Serializer\Groups(['audit_log.user'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column]
    #[Serializer\Groups(['audit_log.user'])]
    private bool $isVerified = false;

    #[ORM\Column(updatable: false, columnDefinition: "DATETIME not null default current_timestamp")]
    #[Serializer\Groups(['audit_log.user'])]
    private DateTimeImmutable $created_at;

    #[ORM\Column(columnDefinition: "DATETIME not null default current_timestamp on update current_timestamp")]
    #[Serializer\Groups(['audit_log.user'])]
    private DateTimeImmutable $updated_at;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'user')]
    private Collection $articles;

    /**
     * @var Collection<int, AuditLog>
     */
    #[ORM\OneToMany(targetEntity: AuditLog::class, mappedBy: 'user')]
    private Collection $auditLogs;

    #[ORM\Column(enumType: SupportedLocale::class, options: ['default' => SupportedLocale::fr->value])]
    private SupportedLocale $locale = SupportedLocale::fr;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->auditLogs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setUser($this);
        }

        return $this;
    }

    public function removeArticle(Article $article, self $newUser): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getUser() === $this) {
                $article->setUser($newUser);
            }
        }

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

    public function isWriter(): bool
    {
        return \in_array('ROLE_WRITER', $this->getRoles(), true);
    }

    public function isAdmin(): bool
    {
        return \in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function getAuditLogs(): Collection
    {
        return $this->auditLogs;
    }

    public function addAuditLog(AuditLog $auditLog): static
    {
        if (!$this->auditLogs->contains($auditLog)) {
            $this->auditLogs->add($auditLog);
            $auditLog->setUser($this);
        }

        return $this;
    }

    public function removeAuditLog(AuditLog $auditLog): static
    {
        if ($this->auditLogs->removeElement($auditLog)) {
            // set the owning side to null (unless already changed)
            if ($auditLog->getUser() === $this) {
                $auditLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Return a Pascal-cased version of the part that comes before that @ in the user's email.
     *
     * Ie: "florent.poujol@example.com" > "Florent Poujol"
     */
    public function getName(): string
    {
        [$name, ] = explode('@', $this->getEmail(),2);

        $name = str_replace(['-', '_'], '.', $name);

        $names = explode('.', $name);
        foreach ($names as $i => $_name) {
            $names[$i][0] = strtoupper($_name[0]);
        }

        return implode(' ', $names);
    }

    #[Serializer\Groups(['audit_log.user'])]
    public function getObfuscatedPassword(): string
    {
        // ie: "$2y$13$RV..." this is enough to show that the password hash has changed
        return substr($this->password, 0, 9) . '...';
    }

    public function getLocale(): SupportedLocale
    {
        return $this->locale;
    }

    public function setLocale(SupportedLocale $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
