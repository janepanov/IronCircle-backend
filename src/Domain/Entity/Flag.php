<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ArchivableTrait;
use App\Domain\Behavior\IArchivable;
use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\ValueObject\FlagID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'flags')]
#[ODM\Index(keys: ['status' => 'asc', 'createdAt' => 'desc'], options: ['background' => true])]
final class Flag implements ITimestampable, IArchivable
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\ReferenceOne(targetDocument: Post::class, storeAs: 'id')]
    private Post $target;

    #[ODM\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private User $reporter;

    #[ODM\Field(type: 'string')]
    private string $reason;

    #[ODM\Field(type: 'string')]
    private string $status = 'open';

    public function __construct(
        FlagID $id,
        Post $target,
        User $reporter,
        string $reason
    ) {
        $this->id = (string) $id;
        $this->target = $target;
        $this->reporter = $reporter;
        $this->reason = $reason;
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
    }

    public function getId(): FlagID
    {
        return FlagID::fromString($this->id);
    }

    public function getTarget(): Post
    {
        return $this->target;
    }

    public function getReporter(): User
    {
        return $this->reporter;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function changeStatus(string $status): void
    {
        $this->status = $status;
        $this->touchUpdatedAt();
    }
}