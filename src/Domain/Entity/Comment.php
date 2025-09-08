<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Behavior\ArchivableTrait;
use App\Domain\Behavior\IArchivable;
use App\Domain\Behavior\ITimestampable;
use App\Domain\Behavior\TimestampableTrait;
use App\Domain\ValueObject\CommentID;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'comments')]
#[ODM\Index(keys: ['post' => 'asc', 'createdAt' => 'asc'], options: ['background' => true])]
#[ODM\Index(keys: ['author' => 'asc'], options: ['background' => true])]
final class Comment implements ITimestampable, IArchivable
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ODM\Id(strategy: 'NONE', type: 'string')]
    private string $id;

    #[ODM\ReferenceOne(targetDocument: Post::class, storeAs: 'id')]
    private Post $post;

    #[ODM\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private User $author;

    #[ODM\Field(type: 'string')]
    private string $body;

    #[ODM\ReferenceOne(targetDocument: Comment::class, storeAs: 'id', nullable: true)]
    private ?Comment $parent = null;

    public function __construct(
        CommentID $id,
        Post $post,
        User $author,
        string $body,
        ?Comment $parent = null
    ) {
        $this->id = (string) $id;
        $this->post = $post;
        $this->author = $author;
        $this->body = $body;
        $this->parent = $parent;
        $this->ensureCreatedAt();
        $this->touchUpdatedAt();
    }

    public function getId(): CommentID
    {
        return CommentID::fromString($this->id);
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParent(): ?Comment
    {
        return $this->parent;
    }

    public function editBody(string $body): void
    {
        $this->body = $body;
        $this->touchUpdatedAt();
    }
}