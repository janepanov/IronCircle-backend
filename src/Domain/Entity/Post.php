<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ArchivableTrait;
use App\Domain\Behavior\IArchivable;
use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\Enum\ArticleStatus;
use App\Domain\ValueObject\PostID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'posts')]
#[ODM\Index(keys: ['circle' => 'asc', 'createdAt' => 'desc'], options: ['background' => true])]
#[ODM\Index(keys: ['author' => 'asc'], options: ['background' => true])]
#[ODM\Index(keys: ['status' => 'asc'], options: ['background' => true])]
#[ODM\Index(keys: ['searchText' => 'text'])]
final class Post implements ITimestampable, IArchivable
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\ReferenceOne(targetDocument: Circle::class, storeAs: 'id')]
    private Circle $circle;

    #[ODM\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private User $author;

    #[ODM\Field(type: 'string')]
    private string $title;

    #[ODM\Field(type: 'string')]
    private string $body;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $excerpt = null;

    #[ODM\Field(type: 'collection')]
    private array $attachments = [];

    #[ODM\Field(type: 'string')]
    private string $status = ArticleStatus::DRAFT->value;

    #[ODM\Field(type: 'int')]
    private int $commentCount = 0;

    #[ODM\Field(type: 'int')]
    private int $voteScore = 0;

    #[ODM\ReferenceOne(targetDocument: AiSummary::class, storeAs: 'id', nullable: true)]
    private ?AiSummary $aiSummary = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $searchText = null;

    public function __construct(
        PostID $id,
        Circle $circle,
        User $author,
        string $title,
        string $body,
        array $attachments = [],
        string $status = ArticleStatus::DRAFT->value
    ) {
        $this->id = (string) $id;
        $this->circle = $circle;
        $this->author = $author;
        $this->title = $title;
        $this->body = $body;
        $this->attachments = $attachments;
        $this->status = $status;
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
        $this->recomputeSearchText();
    }

    public function getId(): PostID
    {
        return PostID::fromString($this->id);
    }

    public function getCircle(): Circle
    {
        return $this->circle;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getStatus(): ArticleStatus
    {
        return ArticleStatus::from($this->status);
    }

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function getVoteScore(): int
    {
        return $this->voteScore;
    }

    public function getAiSummary(): ?AiSummary
    {
        return $this->aiSummary;
    }

    public function getSearchText(): ?string
    {
        return $this->searchText;
    }

    public function changeTitle(string $title): void
    {
        $this->title = $title;
        $this->touchUpdatedAt();
        $this->recomputeSearchText();
    }

    public function changeBody(string $body): void
    {
        $this->body = $body;
        $this->touchUpdatedAt();
        $this->recomputeSearchText();
    }

    public function setExcerpt(?string $excerpt): void
    {
        $this->excerpt = $excerpt;
        $this->touchUpdatedAt();
    }

    public function addAttachment(array $attachment): void
    {
        $this->attachments[] = $attachment;
        $this->touchUpdatedAt();
    }

    public function setStatus(ArticleStatus $status): void
    {
        $current = ArticleStatus::from($this->status);
        if ($current === $status) {
            return;
        }
        if (!$current->canTransition($status)) {
            return;
        }
        $this->status = $status->value;
        $this->touchUpdatedAt();
    }

    public function incrementCommentCount(int $by = 1): void
    {
        $this->commentCount += $by;
        $this->touchUpdatedAt();
    }

    public function decrementCommentCount(int $by = 1): void
    {
        $this->commentCount = max(0, $this->commentCount - $by);
        $this->touchUpdatedAt();
    }

    public function changeVoteScore(int $delta): void
    {
        $this->voteScore += $delta;
        $this->touchUpdatedAt();
    }

    public function setAiSummary(?AiSummary $summary): void
    {
        $this->aiSummary = $summary;
        $this->touchUpdatedAt();
    }

    private function recomputeSearchText(): void
    {
        $this->searchText = trim($this->title . "\n\n" . substr($this->body, 0, 10000));
    }
}