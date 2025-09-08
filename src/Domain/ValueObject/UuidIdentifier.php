<?php
declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidIdentifier implements IIdentifier
{
    protected readonly UuidInterface $value;

    public static function fromString(string $string): static
    {
        try {
            return new static(Uuid::fromString($string));
        } catch (InvalidUuidStringException $exception) {
            throw new \InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    protected function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    public function equals(?IIdentifier $other): bool
    {
        return $other !== null && (string) $this->value === (string) $other->value;
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }
}