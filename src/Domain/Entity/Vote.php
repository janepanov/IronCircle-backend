<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\ValueObject\VoteID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'votes')]
#[ODM\Index(keys: ['target' => 'asc', 'user' => 'asc'], options: ['unique' => true, 'background' => true])]
#[ODM\Index(keys: ['target' => 'asc', 'value' => 'asc'], options: ['background' => true])]
final class Vote implements ITimestampable
{
    use TimestampableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\ReferenceOne(targetDocument: Post::class, storeAs: 'id')]
    private Post $target;

    #[ODM\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private User $user;

    #[ODM\Field(type: 'int')]
    private int $value;

    public function __construct(
        VoteID $id,
        Post $target,
        User $user,
        int $value
    ) {
        if ($value !== 1 && $value !== -1) {
            throw new \InvalidArgumentException('Vote value must be 1 or -1.');
        }
        $this->id = (string) $id;
        $this->target = $target;
        $this->user = $user;
        $this->value = $value;
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
    }

    public function getId(): VoteID
    {
        return VoteID::fromString($this->id);
    }

    public function getTarget(): Post
    {
        return $this->target;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function changeValue(int $value): void
    {
        if ($value !== 1 && $value !== -1) {
            throw new \InvalidArgumentException('Vote value must be 1 or -1.');
        }
        $this->value = $value;
        $this->touchUpdatedAt();
    }
}