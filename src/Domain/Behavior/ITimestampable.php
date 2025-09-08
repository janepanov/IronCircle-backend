<?php
declare(strict_types=1);

namespace App\Domain\Behavior;

interface ITimestampable
{
    public function setCreatedAt(\DateTimeImmutable $createdAt): void;

    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void;

    public function getUpdatedAt(): ?\DateTimeImmutable;
}