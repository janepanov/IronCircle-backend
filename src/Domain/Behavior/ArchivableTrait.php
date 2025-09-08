<?php
declare(strict_types=1);

namespace App\Domain\Behavior;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

trait ArchivableTrait
{
    #[ODM\Field(type: 'date', nullable: true)]
    protected ?\DateTimeImmutable $archivedAt = null;

    public function archive(?\DateTimeImmutable $at = null): void
    {
        $this->archivedAt = $at ?? new \DateTimeImmutable();
    }

    public function unarchive(): void
    {
        $this->archivedAt = null;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }
}