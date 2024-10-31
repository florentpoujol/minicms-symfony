<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasAutomaticTimestamps
{
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PostUpdate] // note that for updates, the property do not appear in the audit logs (even with the Preflush event)
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}