<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ArchivableTrait;
use App\Domain\Behavior\IArchivable;
use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\ValueObject\CircleID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'circles')]
#[ODM\Index(keys: ['slug' => 'asc'], options: ['unique' => true, 'background' => true])]
#[ODM\Index(keys: ['owner' => 'asc'], options: ['background' => true])]
final class Circle implements ITimestampable, IArchivable
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\Field(type: 'string')]
    private string $slug;

    #[ODM\Field(type: 'string')]
    private string $name;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ODM\Field(type: 'bool')]
    private bool $isPublic = true;

    #[ODM\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private User $owner;

    #[ODM\ReferenceMany(targetDocument: User::class, storeAs: 'id')]
    private array $members = [];

    #[ODM\Field(type: 'hash')]
    private array $meta = ['postCount' => 0];

    public function __construct(
        CircleID $id,
        string $slug,
        string $name,
        User $owner,
        ?string $description = null,
        bool $isPublic = true,
        array $members = []
    ) {
        $this->id = (string) $id;
        $this->slug = $slug;
        $this->name = $name;
        $this->owner = $owner;
        $this->description = $description;
        $this->isPublic = $isPublic;
        $this->members = array_values($members);
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
    }

    public function getId(): CircleID
    {
        return CircleID::fromString($this->id);
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
        $this->touchUpdatedAt();
    }

    public function changeDescription(?string $description): void
    {
        $this->description = $description;
        $this->touchUpdatedAt();
    }

    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
        $this->touchUpdatedAt();
    }

    public function addMember(User $user): void
    {
        if (!in_array($user, $this->members, true)) {
            $this->members[] = $user;
            $this->touchUpdatedAt();
        }
    }

    public function removeMember(User $user): void
    {
        $this->members = array_values(array_filter($this->members, static fn($u) => $u !== $user));
        $this->touchUpdatedAt();
    }

    public function incrementPostCount(int $by = 1): void
    {
        $this->meta['postCount'] = ($this->meta['postCount'] ?? 0) + $by;
        $this->touchUpdatedAt();
    }
}