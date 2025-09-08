<?php
declare(strict_types=1);

namespace App\Domain\Behavior;

interface IArchivable
{
    public function archive(?\DateTimeImmutable $at = null): void;

    public function unarchive(): void;

    public function getArchivedAt(): ?\DateTimeImmutable;

    public function isArchived(): bool;
}