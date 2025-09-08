<?php
declare(strict_types=1);

namespace App\Domain\Behavior;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

trait TimestampableTrait
{
    #[ODM\Field(type: 'date', nullable: true)]
    protected ?\DateTimeImmutable $createdAt = null;

    #[ODM\Field(type: 'date', nullable: true)]
    protected ?\DateTimeImmutable $updatedAt = null;

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    protected function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    protected function ensureCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
}