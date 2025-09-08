<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\ValueObject\AiSummaryID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'ai_summaries')]
#[ODM\Index(keys: ['target' => 'asc'], options: ['background' => true])]
#[ODM\Index(keys: ['expiresAt' => 'asc'], options: ['expireAfterSeconds' => 0])]
final class AiSummary implements ITimestampable
{
    use TimestampableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\ReferenceOne(targetDocument: Post::class, storeAs: 'id')]
    private Post $target;

    #[ODM\Field(type: 'string')]
    private string $model;

    #[ODM\Field(type: 'string')]
    private string $promptHash;

    #[ODM\Field(type: 'string')]
    private string $summary;

    #[ODM\Field(type: 'int')]
    private int $tokensUsed = 0;

    #[ODM\Field(type: 'date', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct(
        AiSummaryID $id,
        Post $target,
        string $model,
        string $promptHash,
        string $summary,
        int $tokensUsed = 0,
        ?\DateTimeImmutable $expiresAt = null
    ) {
        $this->id = (string) $id;
        $this->target = $target;
        $this->model = $model;
        $this->promptHash = $promptHash;
        $this->summary = $summary;
        $this->tokensUsed = $tokensUsed;
        $this->expiresAt = $expiresAt;
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
    }

    public function getId(): AiSummaryID
    {
        return AiSummaryID::fromString($this->id);
    }

    public function getTarget(): Post
    {
        return $this->target;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPromptHash(): string
    {
        return $this->promptHash;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getTokensUsed(): int
    {
        return $this->tokensUsed;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= new \DateTimeImmutable();
    }
}