<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ArchivableTrait;
use App\Domain\Behavior\IArchivable;
use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\Enum\UserRole;
use App\Domain\ValueObject\UserID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'users')]
#[ODM\Index(keys: ['username' => 'asc'], options: ['unique' => true, 'background' => true])]
#[ODM\Index(keys: ['email' => 'asc'], options: ['unique' => true, 'background' => true])]
#[ODM\Index(keys: ['lastActiveAt' => 'desc'], options: ['background' => true])]
final class User implements ITimestampable, IArchivable
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\Field(type: 'string')]
    private string $username;

    #[ODM\Field(type: 'string')]
    private string $email;

    #[ODM\Field(type: 'string')]
    private string $passwordHash;

    #[ODM\Field(type: 'collection')]
    private array $roles = [UserRole::MEMBER->value];

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $displayName = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $bio = null;

    #[ODM\Field(type: 'date', nullable: true)]
    private ?\DateTimeImmutable $lastActiveAt = null;

    public function __construct(
        UserID $id,
        string $username,
        string $email,
        string $passwordHash,
        array $roles = [UserRole::MEMBER->value],
        ?string $displayName = null,
        ?string $bio = null
    ) {
        $this->id = (string) $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->roles = array_values($roles);
        $this->displayName = $displayName;
        $this->bio = $bio;
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
    }

    public function getId(): UserID
    {
        return UserID::fromString($this->id);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function changeUsername(string $username): void
    {
        $this->username = $username;
        $this->touchUpdatedAt();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function changeEmail(string $email): void
    {
        $this->email = $email;
        $this->touchUpdatedAt();
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function changePasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->touchUpdatedAt();
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = array_values($roles);
        $this->touchUpdatedAt();
    }

    public function addRole(string $role): void
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
            $this->touchUpdatedAt();
        }
    }

    public function removeRole(string $role): void
    {
        $this->roles = array_values(array_filter($this->roles, static fn($r) => $r !== $role));
        $this->touchUpdatedAt();
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function changeDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
        $this->touchUpdatedAt();
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function changeBio(?string $bio): void
    {
        $this->bio = $bio;
        $this->touchUpdatedAt();
    }

    public function getLastActiveAt(): ?\DateTimeImmutable
    {
        return $this->lastActiveAt;
    }

    public function touchLastActiveAt(?\DateTimeImmutable $at = null): void
    {
        $this->lastActiveAt = $at ?? new \DateTimeImmutable();
        $this->touchUpdatedAt();
    }

    public function isAdmin(): bool
    {
        return in_array(UserRole::ADMIN->value, $this->roles, true);
    }
}